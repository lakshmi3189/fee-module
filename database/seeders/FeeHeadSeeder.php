<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeeHeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = ['Other Fee'];
        foreach ($data as $val) {
            DB::table('fm_fee_heads')->insert([
                'fee_head_name' => $val,
                'version_no' => 0,
                'status' => 1,
                'json_logs' => '{"fee_head_type": ' . $val . ', "version_no":0, "status":1}',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
