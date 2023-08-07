<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinancialYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $section = ['2023-2024'];
        foreach ($section as $val) {
            DB::table('ms_financial_years')->insert([
                'financial_year' => $val,
                'version_no' => 0,
                'status' => 1,
                'json_logs' => '{"financial_year": ' . $val . ', "version_no":0, "status":1}',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
