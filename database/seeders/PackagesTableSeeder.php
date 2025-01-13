<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Package;

class PackagesTableSeeder extends Seeder
{
    public function run(): void
    {
        Package::create([
            'name' => 'normal',
            'amount' => 100.00, // Example amount
            'description' => 'Basic package with limited features.',
        ]);

        Package::create([
            'name' => 'trader',
            'amount' => 200.00, // Example amount
            'description' => 'Advanced package for traders.',
        ]);

        Package::create([
            'name' => 'enterprise',
            'amount' => 500.00, // Example amount
            'description' => 'Premium package for enterprise users.',
        ]);
    }
}