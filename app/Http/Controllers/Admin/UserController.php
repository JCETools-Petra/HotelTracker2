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
        $properties = Property::all();
        $roles = [
            'admin' => 'Admin',
            'owner' => 'Owner',
            'pengurus' => 'Pengurus',
            'pengguna_properti' => 'Pengguna Properti',
            'sales' => 'Sales',
            'hk' => 'Housekeeping',
            'online_ecommerce' => 'E-Commerce',
        ];
        return view('admin.users.create', compact('properties', 'roles'));
    }

    public function store(Request $request)
    {
        $rolesRequiringProperty = ['pengguna_properti', 'sales', 'online_ecommerce', 'hk'];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            // Perbaikan: Tambahkan 'pengurus' ke daftar peran yang valid
            'role' => ['required', Rule::in(['admin', 'owner', 'pengurus', 'pengguna_properti', 'sales', 'online_ecommerce', 'hk'])],
            // Perbaikan: property_id hanya wajib jika rolenya ada di dalam array $rolesRequiringProperty
            'property_id' => [Rule::requiredIf(in_array($request->input('role'), $rolesRequiringProperty)), 'nullable', 'exists:properties,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'property_id' => in_array($validated['role'], ['admin', 'owner', 'pengurus']) ? null : $validated['property_id'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dibuat.');
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(User $user)
    {
        $properties = Property::all();
        $roles = [
            'admin' => 'Admin',
            'owner' => 'Owner',
            'pengurus' => 'Pengurus',
            'pengguna_properti' => 'Pengguna Properti',
            'sales' => 'Sales',
            'hk' => 'Housekeeping',
            'online_ecommerce' => 'E-Commerce',
        ];
        return view('admin.users.edit', compact('user', 'properties', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $rolesRequiringProperty = ['pengguna_properti', 'sales', 'online_ecommerce', 'hk'];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            // Perbaikan: Tambahkan 'pengurus' ke daftar peran yang valid
            'role' => ['required', Rule::in(['admin', 'owner', 'pengurus', 'pengguna_properti', 'sales', 'online_ecommerce', 'hk'])],
            // Perbaikan: property_id hanya wajib jika rolenya ada di dalam array $rolesRequiringProperty
            'property_id' => [Rule::requiredIf(in_array($request->input('role'), $rolesRequiringProperty)), 'nullable', 'exists:properties,id'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->property_id = in_array($validated['role'], ['admin', 'owner', 'pengurus']) ? null : $validated['property_id'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

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