<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //specifying seeder name

        $this->call(CategorySeeder::class);
        $this->call(ClassMasterSeeder::class);
        $this->call(FeeHeadSeeder::class);
        $this->call(FeeHeadTypeSeeder::class);
        $this->call(FinancialYearSeeder::class);
        $this->call(MonthSeeder::class);
        $this->call(SectionSeeder::class);
        $this->call(UserSeeder::class);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
