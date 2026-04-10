<?php

namespace App\Http\Controllers;

use App\Models\InputAspirasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReviewController extends Controller
{
    /**
     * Get all pending complaints for Admin Sistem review.
     */
    public function pending(): JsonResponse
    {
        // Auto-archive old complaints first
        $this->performAutoArchive();

        $complaints = InputAspirasi::pending()
            ->select('id_pelaporan', 'nis', 'id_kategori', 'lokasi', 'foto_dokumentasi', 'keterangan', 'status_review', 'created_at')
            ->with([
                'siswa:nis,nama,kelas',
                'kategori:id_kategori,ket_kategori'
            ])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $complaints,
        ]);
    }

    /**
     * Accept a complaint — makes it visible to Pelaksana.
     */
    public function accept(int $id): JsonResponse
    {
        $complaint = InputAspirasi::findOrFail($id);

        if ($complaint->status_review !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengaduan ini sudah direview sebelumnya.',
            ], 422);
        }

        $complaint->status_review = 'diterima';
        $complaint->save();

        return response()->json([
            'success' => true,
            'message' => 'Pengaduan diterima dan diteruskan ke pelaksana.',
            'data' => $complaint->load(['siswa', 'kategori']),
        ]);
    }

    /**
     * Reject a complaint — moves it to archive.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $complaint = InputAspirasi::findOrFail($id);

        if ($complaint->status_review !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pengaduan ini sudah direview sebelumnya.',
            ], 422);
        }

        $complaint->status_review = 'ditolak';
        $complaint->save();

        return response()->json([
            'success' => true,
            'message' => 'Pengaduan ditolak dan dipindahkan ke arsip.',
            'data' => $complaint->load(['siswa', 'kategori']),
        ]);
    }

    /**
     * Get archived complaints (rejected + auto-archived > 2 weeks).
     */
    public function arsip(): JsonResponse
    {
        $complaints = InputAspirasi::ditolak()
            ->select('id_pelaporan', 'nis', 'id_kategori', 'lokasi', 'keterangan', 'status_review', 'created_at')
            ->with([
                'siswa:nis,nama,kelas',
                'kategori:id_kategori,ket_kategori'
            ])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $complaints,
        ]);
    }

    /**
     * Manually trigger auto-archive of old pending complaints (> 2 weeks).
     */
    public function autoArchive(): JsonResponse
    {
        $count = $this->performAutoArchive();

        return response()->json([
            'success' => true,
            'message' => "{$count} pengaduan lama telah diarsipkan otomatis.",
            'archived_count' => $count,
        ]);
    }

    /**
     * Internal: auto-archive pending complaints older than 2 weeks.
     */
    private function performAutoArchive(): int
    {
        return InputAspirasi::where('status_review', 'pending')
            ->where('created_at', '<', Carbon::now()->subWeeks(2))
            ->update(['status_review' => 'ditolak']);
    }
}
