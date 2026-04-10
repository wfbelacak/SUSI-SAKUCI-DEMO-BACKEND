<?php

namespace Database\Seeders;

use App\Models\Siswa;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siswaData = [
            [
                'nis' => 12001,
                'nama' => 'Ahmad Rizki Pratama',
                'password' => 'siswa123',
                'kelas' => 'XII RPL 1',
                'dibuat_pada' => now(),
                'terakhir_update' => now(),
            ],
            [
                'nis' => 12002,
                'nama' => 'Siti Nurhaliza',
                'password' => 'siswa123',
                'kelas' => 'XII RPL 1',
                'dibuat_pada' => now(),
                'terakhir_update' => now(),
            ],
            [
                'nis' => 12003,
                'nama' => 'Budi Santoso',
                'password' => 'siswa123',
                'kelas' => 'XII RPL 2',
                'dibuat_pada' => now(),
                'terakhir_update' => now(),
            ],
            [
                'nis' => 11001,
                'nama' => 'Dewi Lestari',
                'password' => 'siswa123',
                'kelas' => 'XI RPL 1',
                'dibuat_pada' => now(),
                'terakhir_update' => now(),
            ],
            [
                'nis' => 11002,
                'nama' => 'Andi Wijaya',
                'password' => 'siswa123',
                'kelas' => 'XI RPL 2',
                'dibuat_pada' => now(),
                'terakhir_update' => now(),
            ],
        ];

        foreach ($siswaData as $siswa) {
            Siswa::create($siswa);
        }
    }
}
