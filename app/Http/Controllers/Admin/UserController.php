<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('property')->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $properties = Property::orderBy('name')->get();
        $roles = [
            'admin' => 'Admin',
            'owner' => 'Owner',
            'hk' => 'Housekeeping (HK)',
            'pengguna_properti' => 'Pengguna Properti',
            'sales' => 'Sales',
            'online_ecommerce' => 'Online Ecommerce',
        ];
        return view('admin.users.create', compact('properties', 'roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage-data');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in(['admin', 'owner', 'hk', 'pengguna_properti', 'sales', 'online_ecommerce'])],
            'property_id' => ['nullable', 'required_if:role,pengguna_properti,sales,online_ecommerce,hk', 'exists:properties,id'],
        ]);

        $data = $request->only('name', 'email', 'role', 'property_id');
        $data['password'] = Hash::make($request->password);

        if ($request->role === 'admin' || $request->role === 'owner') {
            $data['property_id'] = null;
        }

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna baru berhasil dibuat.');
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(User $user)
    {
        $properties = Property::orderBy('name')->get();
        $roles = [
            'admin' => 'Admin',
            'owner' => 'Owner',
            'hk' => 'Housekeeping (HK)',
            'pengguna_properti' => 'Pengguna Properti',
            'sales' => 'Sales',
            'online_ecommerce' => 'Online Ecommerce',
        ];
        return view('admin.users.edit', compact('user', 'properties', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('manage-data');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in(['admin', 'owner', 'hk', 'pengguna_properti', 'sales', 'online_ecommerce'])],
            'property_id' => ['nullable', 'required_if:role,pengguna_properti,sales,online_ecommerce,hk', 'exists:properties,id'],
        ]);

        $data = $request->only('name', 'email', 'role', 'property_id');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->role === 'admin' || $request->role === 'owner') {
            $data['property_id'] = null;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->authorize('manage-data');
        if ($user->id === 1) {
            return redirect()->route('admin.users.index')->with('error', 'Super Admin tidak dapat dihapus.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dipindahkan ke sampah.');
    }
    
    public function trashed()
    {
        $users = User::onlyTrashed()->with('property')->latest()->paginate(10);
        return view('admin.users.trashed', compact('users'));
    }

    public function restore($id)
    {
        User::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->route('admin.users.trashed')->with('success', 'Pengguna berhasil dipulihkan.');
    }

    public function forceDelete($id)
    {
        User::onlyTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('admin.users.trashed')->with('success', 'Pengguna berhasil dihapus permanen.');
    }
}