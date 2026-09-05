<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(): View
    {
        $addresses = auth()->user()->addresses()->latest()->get();

        return view('shop.addresses.index', compact('addresses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAddress($request);
        $data['is_default'] = $request->boolean('is_default');
        if ($data['is_default']) {
            auth()->user()->addresses()->update(['is_default' => false]);
        }
        auth()->user()->addresses()->create($data);

        return back()->with('status', 'Address saved.');
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeUser($address);
        $data = $this->validateAddress($request);
        $data['is_default'] = $request->boolean('is_default');
        if ($data['is_default']) {
            auth()->user()->addresses()->update(['is_default' => false]);
        }
        $address->update($data);

        return back()->with('status', 'Address updated.');
    }

    public function destroy(Address $address): RedirectResponse
    {
        $this->authorizeUser($address);
        $address->delete();

        return back()->with('status', 'Address removed.');
    }

    private function authorizeUser(Address $address): void
    {
        if ((int) $address->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'full_name' => 'required|string|max:120',
            'phone' => 'required|string|max:32',
            'line1' => 'required|string|max:255',
            'line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:120',
            'state' => 'nullable|string|max:120',
            'postal_code' => 'required|string|max:32',
            'country' => 'required|string|size:2',
        ]);
    }
}
