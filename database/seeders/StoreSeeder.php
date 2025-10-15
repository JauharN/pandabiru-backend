<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('stores')->insert([
            [
                'code' => '150101',
                'name' => 'Toko Indojuni',
                'address' => 'Jl Palem RT 01/RW 02 Tamansari',
                'owner' => 'Junidi',
                'phone' => '081234567890',
                'open_hour' => '08:00',
                'close_hour' => '22:00',
                'image_url' => 'toko_indojuni.jpg'
            ],
            [
                'code' => '150102',
                'name' => 'Toko Tintin',
                'address' => 'Jl Mawar RT 08/RW 01 Coblong',
                'owner' => 'Widodo',
                'phone' => '0812-3456-7890',
                'open_hour' => '08:00',
                'close_hour' => '22:00',
                'image_url' => 'toko_tintin.jpg',
            ],
            [
                'code' => '150103',
                'name' => 'Toko Warasangit',
                'address' => 'Jl Sigma RT 05/RW 03 Coblong',
                'owner' => 'Solo',
                'phone' => '0812-3456-7890',
                'open_hour' => '08:00',
                'close_hour' => '23:00',
                'image_url' => 'toko_warasangit.jpg',
            ],
        ]);
    }
}
