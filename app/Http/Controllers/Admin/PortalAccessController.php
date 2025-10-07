<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portal;
use Illuminate\Http\Request;

class PortalAccessController extends Controller
{
    public function index()
    {
        return view('admin.portals.index');
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:portals,slug'],
            'callback_url' => ['required', 'url'],
            'logout_url' => ['nullable', 'url'],
            'enforce_mfa' => ['boolean'],
            'scopes' => ['array'],
        ]);

        $payload['client_id'] = $payload['client_id'] ?? str()->uuid()->toString();
        $payload['client_secret'] = $payload['client_secret'] ?? bin2hex(random_bytes(32));

        Portal::create($payload);

        return back()->with('status', __('Portal registered successfully.'));
    }

    public function update(Request $request, Portal $portal)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'callback_url' => ['required', 'url'],
            'logout_url' => ['nullable', 'url'],
            'enforce_mfa' => ['boolean'],
            'scopes' => ['array'],
        ]);

        $portal->update($payload);

        return back()->with('status', __('Portal updated successfully.'));
    }
}
