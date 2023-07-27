<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('admission_no');
            $table->string('roll_no');
            $table->string('full_name');
            $table->integer('class_id');
            $table->string('class_name');
            $table->integer('section_id');
            $table->string('section_name');
            $table->date('dob');
            $table->date('admission_date');
            $table->integer('gender_id');
            $table->string('gender_name');
            $table->string('email')->nullable();
            $table->bigInteger('mobile');
            $table->string('disability');
            $table->integer('category_id');
            $table->string('category_name');
            $table->string('financial_year');
            $table->smallInteger('is_parent_staff');
            $table->bigInteger('created_by')->nullable();   //common for all table   
            $table->string('ip_address');                   //common for all table   
            $table->integer('version_no')->default(0);      //common for all table   
            $table->smallInteger('status')->default(1);     //1-Active, 2-Not Active
            $table->text('json_logs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
