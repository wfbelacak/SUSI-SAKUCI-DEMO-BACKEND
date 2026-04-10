<?php

namespace Database\Seeders;

use App\Models\Aspirasi;
use Illuminate\Database\Seeder;

class AspirasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $aspirasiData = [
            [
                'id_pelaporan' => 1,
                'id_kategori' => 1,
                'id_admin' => 2,
                'status' => 'Proses',
                'feedback' => null,
                'foto_tanggapan' => null,
                'detail_tanggapan' => 'Sedang dalam proses pengecekan oleh teknisi.',
            ],
            [
                'id_pelaporan' => 2,
                'id_kategori' => 2,
                'id_admin' => 2,
                'status' => 'Selesai',
                'feedback' => 5,
                'foto_tanggapan' => 'tanggapan_2.jpg',
                'detail_tanggapan' => 'Komputer sudah diperbaiki dan diupgrade RAM-nya.',
            ],
            [
                'id_pelaporan' => 3,
                'id_kategori' => 3,
                'id_admin' => 3,
                'status' => 'Menunggu',
                'feedback' => null,
                'foto_tanggapan' => null,
                'detail_tanggapan' => null,
            ],
            [
                'id_pelaporan' => 4,
                'id_kategori' => 4,
                'id_admin' => 3,
                'status' => 'Proses',
                'feedback' => null,
                'foto_tanggapan' => null,
                'detail_tanggapan' => 'Ring basket baru sudah dipesan, menunggu pengiriman.',
            ],
        ];

        foreach ($aspirasiData as $aspirasi) {
            Aspirasi::create($aspirasi);
        }
    }
}
