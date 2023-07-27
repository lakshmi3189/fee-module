<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $class = ['SC', 'ST', 'OBC', 'General'];

        foreach ($class as $val) {
            DB::table('ms_categories')->insert([
                'category_name' => $val,
                'version_no' => 0,
                'status' => 1,
                'json_logs' => '{"class_name": ' . $val . ', "version_no":0, "status":1}',
                'created_at' => now(),
                'updated_at' => now()

            ]);
        }
    }
}
