<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'name' => 'Keripik Kentang Xie-xie',
                'barcode' => '8991234560001',
                'size' => '250 mL',
                'image_url' => 'xie_xie.png',
            ],
            [
                'name' => 'Biskuit Kelapa Ni-hao',
                'barcode' => '8991234560002',
                'size' => '100 mL',
                'image_url' => 'ni_hao.png',
            ],
            [
                'name' => 'Coklat Kacang Peng-you',
                'barcode' => '8991234560003',
                'size' => '50 mL',
                'image_url' => 'peng_you.png',
            ],
        ]);
    }
}
