<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['SEIN', 'CATE', 'COADM', 'COAMI', 'COPTM', 'COPMA', 'DIRETORIA', 'COPGI', 'BIBLIOTECA', 'SECOM', 'CORON'] as $name) {
            Sector::firstOrCreate(['name' => $name]);
        }
    }
}
