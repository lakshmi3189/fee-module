<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $section = ['A', 'B', 'C', 'D'];

        foreach ($section as $val) {
            DB::table('ms_sections')->insert([
                'section' => $val,
                'version_no' => 0,
                'status' => 1,
                'json_logs' => '{"section": ' . $val . ', "version_no":0, "status":1}',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
