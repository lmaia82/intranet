<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['SEIN', 'CATE', 'COAD', 'COAM', 'COPM', 'CPMA', 'DIRETORIA', 'CPGI', 'BIBLIOTECA'] as $name) {
            Sector::firstOrCreate(['name' => $name]);
        }
    }
}
