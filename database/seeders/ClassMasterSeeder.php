<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $class = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'Play Group', 'Nursery', 'LKG', 'UKG'];

        foreach ($class as $val) {
            DB::table('ms_classes')->insert([
                'class_name' => $val,
                'version_no' => 0,
                'status' => 1,
                'json_logs' => '{"class_name": ' . $val . ', "version_no":0, "status":1}',
                'created_at' => now(),
                'updated_at' => now()

            ]);
        }
    }
}
