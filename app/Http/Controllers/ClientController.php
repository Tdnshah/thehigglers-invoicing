<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\User;
use App\Mail\ClientUserCreated;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
    /**
     * Show the form for creating a new user for a specific client.
     */
    public function createUser(Client $client)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security check: Only Company Admins can do this
        if (!$user->isCompanyAdmin()) {
            abort(403);
        }

        // Security check: Ensure the client belongs to this admin's company
        if ($client->user_id !== $user->id) {
            abort(403);
        }

        return view('clients.create-user', compact('client'));
    }

    /**
     * Store a newly created user for the client.
     */
    public function storeUser(Request $request, Client $client)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->isCompanyAdmin() || $client->user_id !== $currentUser->id) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'client_id' => $client->id,
        ]);
        
        // Send welcome email with credentials
        Mail::to($user->email)->send(new ClientUserCreated($user, $request->password));

        return redirect()->route('clients.index')->with('success', 'User login created for client successfully.');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Only company admins can manage clients
        if (!$user->isCompanyAdmin()) {
            abort(403);
        }

        $clients = $user->clients()->latest()->paginate(10);
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user->isCompanyAdmin()) {
            abort(403);
        }

        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user->isCompanyAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'gst_number' => 'nullable|string|max:20|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        ]);

        $user->clients()->create($validated);

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user->isCompanyAdmin()) {
            abort(403);
        }

        if ($client->user_id !== $user->id) {
            abort(403);
        }
        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user->isCompanyAdmin()) {
            abort(403);
        }

        if ($client->user_id !== $user->id) {
            abort(403);
        }
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user->isCompanyAdmin()) {
            abort(403);
        }

        if ($client->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
            'gst_number' => 'nullable|string|max:20|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user->isCompanyAdmin()) {
            abort(403);
        }

        if ($client->user_id !== $user->id) {
            abort(403);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
