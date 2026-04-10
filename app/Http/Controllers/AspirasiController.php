<?php

namespace App\Http\Controllers;

use App\Models\Aspirasi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AspirasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $aspirasi = Aspirasi::select('id_aspirasi', 'id_pelaporan', 'id_kategori', 'id_admin', 'status', 'feedback', 'detail_tanggapan')
            ->with([
                'inputAspirasi:id_pelaporan,nis,lokasi,keterangan,status_review',
                'inputAspirasi.siswa:nis,nama,kelas',
                'kategori:id_kategori,ket_kategori',
                'admin:id_admin,nama_admin'
            ])
            ->orderByDesc('id_aspirasi')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $aspirasi
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_pelaporan' => 'required|integer|exists:input_aspirasi,id_pelaporan',
            'id_kategori' => 'required|integer|exists:kategori,id_kategori',
            'id_admin' => 'required|integer|exists:admin,id_admin',
            'status' => 'sometimes|in:Menunggu,Proses,Selesai',
            'feedback' => 'nullable|integer',
            'foto_tanggapan' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240',
            'detail_tanggapan' => 'nullable|string',
        ]);

        // Handle file upload for foto_tanggapan
        if ($request->hasFile('foto_tanggapan')) {
            $file = $request->file('foto_tanggapan');
            $filename = time() . '_tanggapan_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/tanggapan'), $filename);
            $validated['foto_tanggapan'] = '/uploads/tanggapan/' . $filename;
        }

        $aspirasi = Aspirasi::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Aspirasi berhasil ditambahkan',
            'data' => $aspirasi->load(['inputAspirasi.siswa', 'kategori', 'admin'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $aspirasi = Aspirasi::with(['inputAspirasi.siswa', 'kategori', 'admin'])->find($id);

        if (!$aspirasi) {
            return response()->json([
                'success' => false,
                'message' => 'Aspirasi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $aspirasi
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $aspirasi = Aspirasi::find($id);

        if (!$aspirasi) {
            return response()->json([
                'success' => false,
                'message' => 'Aspirasi tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'id_pelaporan' => 'sometimes|integer|exists:input_aspirasi,id_pelaporan',
            'id_kategori' => 'sometimes|integer|exists:kategori,id_kategori',
            'id_admin' => 'sometimes|integer|exists:admin,id_admin',
            'status' => 'sometimes|in:Menunggu,Proses,Selesai',
            'feedback' => 'nullable|integer',
            'foto_tanggapan' => 'nullable|string|max:60',
            'detail_tanggapan' => 'nullable|string',
        ]);

        $aspirasi->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Aspirasi berhasil diupdate',
            'data' => $aspirasi->load(['inputAspirasi.siswa', 'kategori', 'admin'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $aspirasi = Aspirasi::find($id);

        if (!$aspirasi) {
            return response()->json([
                'success' => false,
                'message' => 'Aspirasi tidak ditemukan'
            ], 404);
        }

        $aspirasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Aspirasi berhasil dihapus'
        ]);
    }

    /**
     * Update status aspirasi
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $aspirasi = Aspirasi::find($id);

        if (!$aspirasi) {
            return response()->json([
                'success' => false,
                'message' => 'Aspirasi tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:Menunggu,Proses,Selesai',
            'detail_tanggapan' => 'nullable|string',
        ]);

        $aspirasi->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Status aspirasi berhasil diupdate',
            'data' => $aspirasi
        ]);
    }

    /**
     * Get aspirasi by admin
     */
    public function getByAdmin(int $idAdmin): JsonResponse
    {
        $aspirasi = Aspirasi::select('id_aspirasi', 'id_pelaporan', 'id_kategori', 'id_admin', 'status', 'feedback', 'detail_tanggapan')
            ->with([
                'inputAspirasi:id_pelaporan,nis,lokasi,keterangan',
                'inputAspirasi.siswa:nis,nama,kelas',
                'kategori:id_kategori,ket_kategori'
            ])
            ->where('id_admin', $idAdmin)
            ->orderByDesc('id_aspirasi')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $aspirasi
        ]);
    }

    /**
     * Get aspirasi by status
     */
    public function getByStatus(string $status): JsonResponse
    {
        $aspirasi = Aspirasi::select('id_aspirasi', 'id_pelaporan', 'id_kategori', 'id_admin', 'status', 'feedback', 'detail_tanggapan')
            ->with([
                'inputAspirasi:id_pelaporan,nis,lokasi,keterangan',
                'inputAspirasi.siswa:nis,nama,kelas',
                'kategori:id_kategori,ket_kategori',
                'admin:id_admin,nama_admin'
            ])
            ->where('status', $status)
            ->orderByDesc('id_aspirasi')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $aspirasi
        ]);
    }

    /**
     * Update feedback/rating from student
     */
    public function updateFeedback(Request $request, int $id): JsonResponse
    {
        $aspirasi = Aspirasi::find($id);

        if (!$aspirasi) {
            return response()->json([
                'success' => false,
                'message' => 'Aspirasi tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'feedback' => 'required|integer|min:1|max:5',
        ]);

        $aspirasi->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feedback berhasil disimpan',
            'data' => $aspirasi
        ]);
    }
}
