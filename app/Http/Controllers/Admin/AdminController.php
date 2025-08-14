<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use App\Area;
use App\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display a listing of users for management
     */
    public function manageUsers()
    {
        $users = User::orderBy('role', 'asc')->orderBy('name', 'asc')->get();
        $areas = Area::active()->orderBy('name', 'asc')->get();
        
        return view('admin.users.index', compact('users', 'areas'));
    }

    /**
     * Show the form for creating a new user
     */
    public function createUser()
    {
        $areas = Area::active()->orderBy('name', 'asc')->get();
        return view('admin.users.create', compact('areas'));
    }

    /**
     * Store a newly created user
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,worker,customer',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'active' => 'boolean',
            'assigned_area_ids' => 'array',
            'assigned_area_ids.*' => 'exists:areas,id',
        ]);

        // Only include assigned_area_ids for workers
        if ($validated['role'] !== 'worker') {
            $validated['assigned_area_ids'] = null;
        }

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing a user
     */
    public function editUser(User $user)
    {
        $areas = Area::active()->orderBy('name', 'asc')->get();
        return view('admin.users.edit', compact('user', 'areas'));
    }

    /**
     * Update the specified user
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,worker,customer',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'active' => 'boolean',
            'assigned_area_ids' => 'array',
            'assigned_area_ids.*' => 'exists:areas,id',
        ]);

        // Only include assigned_area_ids for workers
        if ($validated['role'] !== 'worker') {
            $validated['assigned_area_ids'] = null;
        }

        // Only update password if provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Toggle user active status
     */
    public function toggleUserStatus(User $user)
    {
        $user->update(['active' => !$user->active]);

        $status = $user->active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success', "User {$status} successfully.");
    }

    /**
     * Delete a user
     */
    public function destroyUser(User $user)
    {
        // Prevent deleting the current admin user
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Show worker assignment page
     */
    public function assignWorkers()
    {
        $workers = User::where('role', 'worker')->orderBy('name', 'asc')->get();
        $areas = Area::active()->orderBy('name', 'asc')->get();
        
        return view('admin.workers.assign', compact('workers', 'areas'));
    }

    /**
     * Update worker area assignments
     */
    public function updateWorkerAssignments(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:users,id',
            'assigned_area_ids' => 'array',
            'assigned_area_ids.*' => 'exists:areas,id',
        ]);

        $worker = User::where('id', $validated['worker_id'])
                      ->where('role', 'worker')
                      ->firstOrFail();

        $worker->update([
            'assigned_area_ids' => $validated['assigned_area_ids'] ?? []
        ]);

        return redirect()->back()
            ->with('success', "Worker assignments updated for {$worker->name}.");
    }

    /**
     * Get worker assignments as JSON (for AJAX requests)
     */
    public function getWorkerAssignments(User $worker)
    {
        if ($worker->role !== 'worker') {
            return response()->json(['error' => 'User is not a worker'], 400);
        }

        return response()->json([
            'worker' => $worker,
            'assigned_area_ids' => $worker->assigned_area_ids ?? [],
            'assigned_areas' => $worker->assignedAreas()
        ]);
    }

    /**
     * Bulk update multiple workers' assignments
     */
    public function bulkUpdateWorkerAssignments(Request $request)
    {
        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.worker_id' => 'required|exists:users,id',
            'assignments.*.area_ids' => 'array',
            'assignments.*.area_ids.*' => 'exists:areas,id',
        ]);

        $updated = 0;
        foreach ($validated['assignments'] as $assignment) {
            $worker = User::where('id', $assignment['worker_id'])
                          ->where('role', 'worker')
                          ->first();
            
            if ($worker) {
                $worker->update([
                    'assigned_area_ids' => $assignment['area_ids'] ?? []
                ]);
                $updated++;
            }
        }

        return redirect()->back()
            ->with('success', "Updated assignments for {$updated} workers.");
    }
}
