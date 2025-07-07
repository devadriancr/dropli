<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create(['code' => '200000', 'name' => 'MMVO']);
        Customer::create(['code' => '400403', 'name' => 'TOYOTA']);
        Customer::create(['code' => '200700', 'name' => 'MNAO']);
        Customer::create(['code' => '400501', 'name' => 'F&P']);
    }
}
