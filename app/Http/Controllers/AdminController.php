<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $admin = Admin::select('id_admin', 'nama_admin', 'username', 'posisi')
            ->orderBy('id_admin')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $admin
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_admin' => 'required|string|max:30',
            'username' => 'required|integer|unique:admin,username',
            'password' => 'required|string|max:15',
            'posisi' => 'required|in:Admin,Pelaksana',
        ]);

        $admin = Admin::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Admin berhasil ditambahkan',
            'data' => $admin
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $admin = Admin::with('aspirasi')->find($id);

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $admin
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'nama_admin' => 'sometimes|string|max:30',
            'username' => 'sometimes|integer|unique:admin,username,' . $id . ',id_admin',
            'password' => 'sometimes|string|max:15',
            'posisi' => 'sometimes|in:Admin,Pelaksana',
        ]);

        $admin->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Admin berhasil diupdate',
            'data' => $admin
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin tidak ditemukan'
            ], 404);
        }

        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin berhasil dihapus'
        ]);
    }

    /**
     * Login admin
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|integer',
            'password' => 'required|string',
        ]);

        // Debug: Log the incoming request data
        \Log::info('Admin Login Attempt', [
            'username' => $validated['username'],
            'password_length' => strlen($validated['password']),
        ]);

        // Find admin by username first
        $admin = Admin::where('username', $validated['username'])->first();

        if (!$admin) {
            \Log::info('Admin not found', ['username' => $validated['username']]);
            return response()->json([
                'success' => false,
                'message' => 'Username tidak ditemukan'
            ], 401);
        }

        // Debug: Log admin data from DB
        \Log::info('Admin found', [
            'id_admin' => $admin->id_admin,
            'nama_admin' => $admin->nama_admin,
            'db_password' => $admin->password,
            'input_password' => $validated['password'],
            'match' => $admin->password === $validated['password'],
        ]);

        // Compare password (plain text comparison since we're not using hashing)
        if ($admin->password !== $validated['password']) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => $admin
        ]);
    }
}
