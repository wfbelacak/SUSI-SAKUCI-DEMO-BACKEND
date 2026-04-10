<?php

namespace Database\Seeders;

use App\Models\InputAspirasi;
use Illuminate\Database\Seeder;

class InputAspirasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inputAspirasiData = [
            [
                'nis' => 12001,
                'id_kategori' => 1,
                'lokasi' => 'Ruang Kelas XII RPL 1',
                'foto_dokumentasi' => 'dokumentasi_1.jpg',
                'keterangan' => 'Proyektor di kelas tidak berfungsi dengan baik, gambar sering berkedip dan kadang mati sendiri.',
            ],
            [
                'nis' => 12002,
                'id_kategori' => 2,
                'lokasi' => 'Laboratorium Komputer 1',
                'foto_dokumentasi' => 'dokumentasi_2.jpg',
                'keterangan' => 'Beberapa komputer di lab 1 sangat lambat dan sering hang saat digunakan untuk praktikum.',
            ],
            [
                'nis' => 12003,
                'id_kategori' => 3,
                'lokasi' => 'Toilet Lantai 2 Gedung Utama',
                'foto_dokumentasi' => 'dokumentasi_3.jpg',
                'keterangan' => 'Air di toilet lantai 2 tidak mengalir dengan lancar, keran sering macet.',
            ],
            [
                'nis' => 11001,
                'id_kategori' => 4,
                'lokasi' => 'Lapangan Basket',
                'foto_dokumentasi' => 'dokumentasi_4.jpg',
                'keterangan' => 'Ring basket sudah rusak dan perlu diganti, berbahaya jika digunakan.',
            ],
            [
                'nis' => 11002,
                'id_kategori' => 8,
                'lokasi' => 'Koridor Lantai 1',
                'foto_dokumentasi' => 'dokumentasi_5.jpg',
                'keterangan' => 'Sampah menumpuk di sudut koridor lantai 1, mohon untuk lebih sering dibersihkan.',
            ],
        ];

        foreach ($inputAspirasiData as $aspirasi) {
            InputAspirasi::create($aspirasi);
        }
    }
}
