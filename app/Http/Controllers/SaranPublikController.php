<?php

namespace App\Http\Controllers;

use App\Models\SaranPublik;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;

class SaranPublikController extends Controller
{
    /**
     * Display a listing of the resource (for admin).
     */
    public function index(): JsonResponse
    {
        $saran = SaranPublik::with('kategori')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $saran
        ]);
    }

    /**
     * Store a newly created public feedback.
     * This is the public endpoint - no authentication required.
     */
    public function store(Request $request): JsonResponse
    {
        // Simple rate limiting by IP - max 5 submissions per hour
        $key = 'saran-publik:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak pengiriman. Silakan coba lagi dalam " . ceil($seconds / 60) . " menit."
            ], 429);
        }
        
        RateLimiter::hit($key, 3600); // 1 hour decay
        
        $validated = $request->validate([
            'nama_pengirim' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'no_telepon' => 'nullable|string|max:20',
            'kategori_pengirim' => 'required|in:Alumni,Orang Tua,Masyarakat Umum,Lainnya',
            'id_kategori' => 'nullable|exists:kategori,id_kategori',
            'isi_saran' => 'required|string|min:10|max:2000',
        ], [
            'nama_pengirim.required' => 'Nama wajib diisi',
            'nama_pengirim.max' => 'Nama maksimal 100 karakter',
            'email.email' => 'Format email tidak valid',
            'kategori_pengirim.required' => 'Pilih kategori pengirim',
            'kategori_pengirim.in' => 'Kategori pengirim tidak valid',
            'isi_saran.required' => 'Isi saran/kritik wajib diisi',
            'isi_saran.min' => 'Isi saran minimal 10 karakter',
            'isi_saran.max' => 'Isi saran maksimal 2000 karakter',
        ]);

        $saran = SaranPublik::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih! Saran/kritik Anda telah berhasil dikirim.',
            'data' => $saran
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $saran = SaranPublik::with('kategori')->find($id);
        
        if (!$saran) {
            return response()->json([
                'success' => false,
                'message' => 'Saran tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $saran
        ]);
    }

    /**
     * Update the status of specific feedback (admin only).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $saran = SaranPublik::find($id);
        
        if (!$saran) {
            return response()->json([
                'success' => false,
                'message' => 'Saran tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:Baru,Dibaca,Ditindaklanjuti',
        ]);

        $saran->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui',
            'data' => $saran
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $saran = SaranPublik::find($id);
        
        if (!$saran) {
            return response()->json([
                'success' => false,
                'message' => 'Saran tidak ditemukan'
            ], 404);
        }

        $saran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Saran berhasil dihapus'
        ]);
    }

    /**
     * Get statistics for admin dashboard.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total' => SaranPublik::count(),
            'baru' => SaranPublik::where('status', 'Baru')->count(),
            'dibaca' => SaranPublik::where('status', 'Dibaca')->count(),
            'ditindaklanjuti' => SaranPublik::where('status', 'Ditindaklanjuti')->count(),
            'by_kategori_pengirim' => SaranPublik::selectRaw('kategori_pengirim, COUNT(*) as total')
                ->groupBy('kategori_pengirim')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
