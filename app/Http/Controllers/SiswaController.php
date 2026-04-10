<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $siswa = Siswa::select('nis', 'nama', 'kelas', 'password', 'is_active', 'dibuat_pada', 'terakhir_update')
            ->orderBy('nama')
            ->get()
            ->makeVisible('password');
        return response()->json([
            'success' => true,
            'data' => $siswa
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nis' => 'required|integer|unique:siswa,nis',
            'nama' => 'required|string|max:60',
            'password' => 'required|string|max:15',
            'kelas' => 'required|string|max:10',
        ]);

        $validated['dibuat_pada'] = now();
        $validated['terakhir_update'] = now();

        $siswa = Siswa::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil ditambahkan',
            'data' => $siswa
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $nis): JsonResponse
    {
        $siswa = Siswa::with('inputAspirasi')->find($nis);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $siswa
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $nis): JsonResponse
    {
        $siswa = Siswa::find($nis);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'nama' => 'sometimes|string|max:60',
            'password' => 'sometimes|string|max:15',
            'kelas' => 'sometimes|string|max:10',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['terakhir_update'] = now();
        $siswa->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil diupdate',
            'data' => $siswa
        ]);
    }

    /**
     * Toggle active status of a siswa account.
     */
    public function toggleActive(int $nis): JsonResponse
    {
        $siswa = Siswa::find($nis);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        }

        $siswa->is_active = !$siswa->is_active;
        $siswa->terakhir_update = now();
        $siswa->save();

        return response()->json([
            'success' => true,
            'message' => $siswa->is_active 
                ? 'Akun siswa berhasil diaktifkan' 
                : 'Akun siswa berhasil dinonaktifkan',
            'data' => $siswa
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $nis): JsonResponse
    {
        $siswa = Siswa::find($nis);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        }

        $siswa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil dihapus'
        ]);
    }

    /**
     * Login siswa
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nis' => 'required|integer',
            'password' => 'required|string',
        ]);

        // Debug: Log the incoming request data
        \Log::info('Siswa Login Attempt', [
            'nis' => $validated['nis'],
            'password_length' => strlen($validated['password']),
        ]);

        // Find siswa by NIS first
        $siswa = Siswa::where('nis', $validated['nis'])->first();

        if (!$siswa) {
            \Log::info('Siswa not found', ['nis' => $validated['nis']]);
            return response()->json([
                'success' => false,
                'message' => 'NIS tidak ditemukan'
            ], 401);
        }

        // Debug: Log siswa data from DB
        \Log::info('Siswa found', [
            'nis' => $siswa->nis,
            'nama' => $siswa->nama,
            'db_password' => $siswa->password,
            'input_password' => $validated['password'],
            'match' => $siswa->password === $validated['password'],
        ]);

        // Compare password (plain text comparison since we're not using hashing)
        if ($siswa->password !== $validated['password']) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah'
            ], 401);
        }

        // Check if account is active (blocked from login)
        if ($siswa->is_active === false) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan. Hubungi admin untuk informasi lebih lanjut.'
            ], 403);  // 403 Forbidden — account exists but access denied
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => $siswa
        ]);
    }
}
