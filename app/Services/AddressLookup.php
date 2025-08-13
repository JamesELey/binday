<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\AppSetting;

class AddressLookup
{
    private function normalisePostcode(string $postcode): string
    {
        return strtoupper(str_replace(' ', '', trim($postcode)));
    }

    public function getCoordsByAutocomplete(string $term): ?array
    {
        $apiKey = config('services.getaddress.api_key');
        $baseUrl = rtrim(config('services.getaddress.base_url'), '/');
        if (empty($apiKey)) {
            return null;
        }

        $autoUrl = $baseUrl . '/autocomplete/' . rawurlencode(trim($term));
        $autoRes = Http::timeout(8)->get($autoUrl, [ 'api-key' => $apiKey ]);
        if (!$autoRes->ok()) {
            return null;
        }
        $auto = $autoRes->json();
        $first = ($auto['suggestions'][0] ?? null);
        $id = $first['id'] ?? null;
        if (!$id) {
            return null;
        }
        $getUrl = $baseUrl . '/get/' . rawurlencode($id);
        $getRes = Http::timeout(8)->get($getUrl, [ 'api-key' => $apiKey ]);
        if (!$getRes->ok()) {
            return null;
        }
        $data = $getRes->json();
        if (!isset($data['latitude'], $data['longitude'])) {
            return null;
        }
        return [
            'lat' => (float) $data['latitude'],
            'lng' => (float) $data['longitude'],
        ];
    }

    public function getCoordsByHouseAndPostcode(string $address, string $postcode): ?array
    {
        $postcode = $this->normalisePostcode($postcode);
        // Extract leading house number (supports 10, 10A, etc.)
        if (!preg_match('/^\s*(\d+[A-Za-z]?)/', $address, $m)) {
            return null;
        }
        $house = $m[1];
        return $this->getCoordsByAutocomplete($house . ' ' . $postcode);
    }

    public function searchByPostcode(string $postcode, ?string $query = null): array
    {
        $provider = AppSetting::get('address_provider', 'getaddress');
        if ($provider === 'postcodes_io') {
            return $this->searchByPostcodePostcodesIo($postcode, $query);
        }
        $postcodeRaw = trim($postcode);
        $postcodeNoSpace = str_replace(' ', '', strtoupper($postcodeRaw));
        $query = $query !== null ? trim($query) : null;

        $apiKey = config('services.getaddress.api_key');
        $baseUrl = rtrim(config('services.getaddress.base_url'), '/');
        if (empty($apiKey)) {
            return [];
        }

        // 1) Try autocomplete first (works well for inputs like "st216dw" and with house numbers)
        $term = $query ? ($query . ' ' . $postcodeNoSpace) : $postcodeNoSpace;
        $autoUrl = $baseUrl . '/autocomplete/' . rawurlencode($term);
        $autoRes = Http::timeout(8)->get($autoUrl, [ 'api-key' => $apiKey ]);
        if ($autoRes->ok()) {
            $auto = $autoRes->json();
            $suggestions = array_map(fn($s) => $s['address'] ?? null, $auto['suggestions'] ?? []);
            $suggestions = array_values(array_filter(array_unique($suggestions ?? [])));
            if (!empty($suggestions)) {
                return array_slice($suggestions, 0, 50);
            }
        }

        // 2) Fallback to find endpoint
        $findUrl = $baseUrl . '/find/' . rawurlencode($postcodeRaw);
        $params = [
            'api-key' => $apiKey,
            'expand' => 'true',
            'sort' => 'true',
        ];
        // If query is numeric like house number, find supports /find/{postcode}/{number}
        if ($query !== null && preg_match('/^\d+[A-Za-z]?$/', $query)) {
            $findUrl = $baseUrl . '/find/' . rawurlencode($postcodeRaw) . '/' . rawurlencode($query);
        }
        $response = Http::timeout(8)->get($findUrl, $params);
        if (!$response->ok()) {
            return [];
        }

        $data = $response->json();
        $addresses = [];
        foreach (($data['addresses'] ?? []) as $address) {
            if (is_string($address)) {
                $formatted = trim($address);
            } else {
                $parts = array_filter([
                    $address['line_1'] ?? null,
                    $address['line_2'] ?? null,
                    $address['line_3'] ?? null,
                    $address['town_or_city'] ?? null,
                    $address['county'] ?? null,
                ]);
                $formatted = implode(', ', $parts);
            }
            if ($formatted !== '') {
                $addresses[] = $formatted;
            }
        }

        if ($query !== null && $query !== '') {
            $q = Str::lower($query);
            $addresses = array_values(array_filter($addresses, function ($a) use ($q) {
                return str_contains(Str::lower($a), $q);
            }));
        }

        $addresses = array_values(array_unique($addresses));
        return array_slice($addresses, 0, 50);
    }

    /**
     * Return rich suggestions including getaddress.io id when available
     * [ ['address' => string, 'id' => string|null], ... ]
     */
    public function suggestionsByPostcode(string $postcode, ?string $query = null): array
    {
        $provider = AppSetting::get('address_provider', 'getaddress');
        if ($provider === 'postcodes_io') {
            return $this->suggestionsByPostcodePostcodesIo($postcode, $query);
        }
        $postcodeRaw = trim($postcode);
        $postcodeNoSpace = str_replace(' ', '', strtoupper($postcodeRaw));
        $query = $query !== null ? trim($query) : null;

        $apiKey = config('services.getaddress.api_key');
        $baseUrl = rtrim(config('services.getaddress.base_url'), '/');
        $results = [];
        if (!empty($apiKey)) {
            $term = $query ? ($query . ' ' . $postcodeNoSpace) : $postcodeNoSpace;
            $autoUrl = $baseUrl . '/autocomplete/' . rawurlencode($term);
            $autoRes = Http::timeout(8)->get($autoUrl, [ 'api-key' => $apiKey ]);
            if ($autoRes->ok()) {
                foreach (($autoRes->json()['suggestions'] ?? []) as $s) {
                    $addr = $s['address'] ?? null;
                    $id = $s['id'] ?? null;
                    if ($addr) { $results[] = ['address' => $addr, 'id' => $id]; }
                }
            }
        }
        if (!empty($results)) {
            // unique by address
            $uniq = [];
            foreach ($results as $r) { $uniq[$r['address']] = $r; }
            return array_values(array_slice($uniq, 0, 50));
        }

        // Fallback: find addresses without id
        $findUrl = $baseUrl . '/find/' . rawurlencode($postcodeRaw);
        $response = Http::timeout(8)->get($findUrl, [ 'api-key' => $apiKey, 'expand' => 'true', 'sort' => 'true' ]);
        if ($response->ok()) {
            foreach (($response->json()['addresses'] ?? []) as $address) {
                $formatted = is_string($address) ? trim($address) : implode(', ', array_filter([
                    $address['line_1'] ?? null,
                    $address['line_2'] ?? null,
                    $address['line_3'] ?? null,
                    $address['town_or_city'] ?? null,
                    $address['county'] ?? null,
                ]));
                if ($formatted !== '') {
                    $results[] = ['address' => $formatted, 'id' => null];
                }
            }
        }
        return array_slice($results, 0, 50);
    }

    private function searchByPostcodePostcodesIo(string $postcode, ?string $query = null): array
    {
        $pc = strtoupper(trim($postcode));
        // Postcodes.io does not provide property-level address lists; just return the postcode if valid
        $res = Http::timeout(8)->get('https://api.postcodes.io/postcodes/' . rawurlencode($pc) . '/validate');
        if ($res->ok() && ($res->json()['result'] ?? false)) {
            // With postcodes.io, we can only return a generic address prompt for the postcode
            return [$pc];
        }
        return [];
    }

    private function suggestionsByPostcodePostcodesIo(string $postcode, ?string $query = null): array
    {
        $pc = strtoupper(trim($postcode));
        $res = Http::timeout(8)->get('https://api.postcodes.io/postcodes/' . rawurlencode($pc) . '/validate');
        if ($res->ok() && ($res->json()['result'] ?? false)) {
            return [['address' => $pc, 'id' => null]];
        }
        return [];
    }

    public function coordsForPostcodePostcodesIo(string $postcode): ?array
    {
        $pc = strtoupper(trim($postcode));
        $res = Http::timeout(8)->get('https://api.postcodes.io/postcodes/' . rawurlencode($pc));
        if ($res->ok()) {
            $j = $res->json();
            if (isset($j['result']['latitude'], $j['result']['longitude'])) {
                return ['lat' => (float)$j['result']['latitude'], 'lng' => (float)$j['result']['longitude']];
            }
        }
        return null;
    }
}


