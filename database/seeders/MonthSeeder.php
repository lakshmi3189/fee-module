<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        foreach ($data as $val) {
            DB::table('ms_months')->insert([
                'month_name' => $val,
                'version_no' => 0,
                'status' => 1,
                'json_logs' => '{"month_name": ' . $val . ', "version_no":0, "status":1}',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
