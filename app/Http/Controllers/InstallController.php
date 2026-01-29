<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class InstallController extends Controller
{
    public function index()
    {
        return view('install.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Company Details
            'company_name' => 'required|string|max:255',
            'gst_number' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'required|string',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // Admin User Details
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $companyData = [
            'name' => $validated['company_name'],
            'gst_number' => $validated['gst_number'],
            'email' => $validated['company_email'],
            'phone' => $validated['company_phone'],
            'address' => $validated['company_address'],
        ];

        // Handle Logo Upload
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('company-logos', 'public');
            $companyData['logo_path'] = $path;
        }

        // Create Company
        $company = Company::create($companyData);

        // Create Admin User
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $company->id,
        ]);

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
