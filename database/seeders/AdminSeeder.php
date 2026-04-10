<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminData = [
            [
                'nama_admin' => 'Super Admin',
                'username' => 1001,
                'password' => 'admin123',
                'posisi' => 'Admin',
            ],
            [
                'nama_admin' => 'Pelaksana Satu',
                'username' => 2001,
                'password' => 'pelaksana123',
                'posisi' => 'Pelaksana',
            ],
            [
                'nama_admin' => 'Pelaksana Dua',
                'username' => 2002,
                'password' => 'pelaksana123',
                'posisi' => 'Pelaksana',
            ],
        ];

        foreach ($adminData as $admin) {
            Admin::create($admin);
        }
    }
}
