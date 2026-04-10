<?php

namespace App\Http\Controllers;

use App\Models\InputAspirasi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class InputAspirasiController extends Controller
{
    /**
     * Display a listing of the resource.
     * Supports optional ?status_review=diterima filter for Pelaksana.
     */
    public function index(Request $request): JsonResponse
    {
        $query = InputAspirasi::select('id_pelaporan', 'nis', 'id_kategori', 'lokasi', 'foto_dokumentasi', 'keterangan', 'status_review', 'created_at')
            ->with([
                'siswa:nis,nama,kelas',
                'kategori:id_kategori,ket_kategori',
                'aspirasi:id_aspirasi,id_pelaporan,status,feedback'
            ]);

        // Allow filtering by status_review (e.g., ?status_review=diterima)
        if ($request->has('status_review')) {
            $query->where('status_review', $request->get('status_review'));
        }

        $inputAspirasi = $query->orderByDesc('id_pelaporan')->get();

        return response()->json([
            'success' => true,
            'data' => $inputAspirasi
        ]);
    }

    /**
     * Get recent complaints (lightweight, for dashboard).
     * Supports optional ?status_review=diterima filter for Pelaksana.
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 5), 20);
        
        $query = InputAspirasi::select('id_pelaporan', 'nis', 'id_kategori', 'lokasi', 'keterangan', 'status_review', 'created_at')
            ->with([
                'siswa:nis,nama,kelas',
                'kategori:id_kategori,ket_kategori',
                'aspirasi:id_aspirasi,id_pelaporan,status'
            ]);

        // Allow filtering by status_review
        if ($request->has('status_review')) {
            $query->where('status_review', $request->get('status_review'));
        }

        $inputAspirasi = $query->orderByDesc('id_pelaporan')
            ->limit($limit)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $inputAspirasi
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nis' => 'required|integer|exists:siswa,nis',
            'id_kategori' => 'required|integer|exists:kategori,id_kategori',
            'lokasi' => 'required|string|max:50',
            'foto_dokumentasi' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'keterangan' => 'required|string',
        ]);

        // Handle file upload
        if ($request->hasFile('foto_dokumentasi')) {
            $file = $request->file('foto_dokumentasi');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/pengaduan'), $filename);
            $validated['foto_dokumentasi'] = '/uploads/pengaduan/' . $filename;
        }

        $inputAspirasi = InputAspirasi::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Input aspirasi berhasil ditambahkan',
            'data' => $inputAspirasi->load(['siswa', 'kategori'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $inputAspirasi = InputAspirasi::with(['siswa', 'kategori', 'aspirasi', 'aspirasi.admin'])->find($id);

        if (!$inputAspirasi) {
            return response()->json([
                'success' => false,
                'message' => 'Input aspirasi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $inputAspirasi
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $inputAspirasi = InputAspirasi::find($id);

        if (!$inputAspirasi) {
            return response()->json([
                'success' => false,
                'message' => 'Input aspirasi tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'nis' => 'sometimes|integer|exists:siswa,nis',
            'id_kategori' => 'sometimes|integer|exists:kategori,id_kategori',
            'lokasi' => 'sometimes|string|max:50',
            'foto_dokumentasi' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'keterangan' => 'sometimes|string',
        ]);

        // Handle file upload
        if ($request->hasFile('foto_dokumentasi')) {
            // Delete old file if exists
            if ($inputAspirasi->foto_dokumentasi) {
                $oldPath = public_path($inputAspirasi->foto_dokumentasi);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            $file = $request->file('foto_dokumentasi');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/pengaduan'), $filename);
            $validated['foto_dokumentasi'] = '/uploads/pengaduan/' . $filename;
        }

        $inputAspirasi->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Input aspirasi berhasil diupdate',
            'data' => $inputAspirasi->load(['siswa', 'kategori'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $inputAspirasi = InputAspirasi::find($id);

        if (!$inputAspirasi) {
            return response()->json([
                'success' => false,
                'message' => 'Input aspirasi tidak ditemukan'
            ], 404);
        }

        // Delete associated file
        if ($inputAspirasi->foto_dokumentasi) {
            $filePath = public_path($inputAspirasi->foto_dokumentasi);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $inputAspirasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Input aspirasi berhasil dihapus'
        ]);
    }

    /**
     * Get input aspirasi by siswa NIS
     */
    public function getBySiswa(int $nis): JsonResponse
    {
        $inputAspirasi = InputAspirasi::select('id_pelaporan', 'nis', 'id_kategori', 'lokasi', 'foto_dokumentasi', 'keterangan', 'status_review', 'created_at')
            ->with([
                'kategori:id_kategori,ket_kategori',
                'aspirasi:id_aspirasi,id_pelaporan,id_admin,status,feedback,detail_tanggapan',
                'aspirasi.admin:id_admin,nama_admin'
            ])
            ->where('nis', $nis)
            ->orderByDesc('id_pelaporan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $inputAspirasi
        ]);
    }
}
