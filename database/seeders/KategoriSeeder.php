<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriData = [
            ['ket_kategori' => 'Fasilitas Kelas'],
            ['ket_kategori' => 'Fasilitas Laboratorium'],
            ['ket_kategori' => 'Fasilitas Toilet'],
            ['ket_kategori' => 'Fasilitas Olahraga'],
            ['ket_kategori' => 'Fasilitas Perpustakaan'],
            ['ket_kategori' => 'Fasilitas Kantin'],
            ['ket_kategori' => 'Infrastruktur'],
            ['ket_kategori' => 'Kebersihan'],
            ['ket_kategori' => 'Keamanan'],
            ['ket_kategori' => 'Lainnya'],
        ];

        foreach ($kategoriData as $kategori) {
            Kategori::create($kategori);
        }
    }
}
