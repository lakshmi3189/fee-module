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
        Schema::create('fm_class_fee_masters', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('fy_id');
            $table->bigInteger('class_id');
            $table->bigInteger('fee_head_type_id');
            $table->bigInteger('fee_head_id');
            $table->bigInteger('month_id');
            $table->integer('fee_amount');
            $table->string('description');
            $table->bigInteger('created_by')->nullable();   //common for all table   
            $table->string('ip_address');                   //common for all table   
            $table->integer('version_no')->default(0);      //common for all table   
            $table->smallInteger('status')->default(1);     //1-Active, 2-Not Active
            $table->text('json_logs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fm_class_fee_masters');
    }
};
