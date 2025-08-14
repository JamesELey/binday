<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function create()
    {
        return view('enquiry.create');
    }
    
    public function store(Request $request)
    {
        // Simple enquiry handling - for now just redirect back with success
        return redirect()->back()->with('success', 'Thank you for your enquiry. We will be in touch soon.');
    }
}
