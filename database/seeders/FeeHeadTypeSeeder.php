<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeeHeadTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = ['Special Month', 'Monthly', 'Yearly'];

        foreach ($data as $val) {
            DB::table('fm_fee_head_types')->insert([
                'fee_head_type' => $val,
                'version_no' => 0,
                'status' => 1,
                'json_logs' => '{"fee_head_type": ' . $val . ', "version_no":0, "status":1}',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
