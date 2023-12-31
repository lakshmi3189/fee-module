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
        Schema::create('fm_fee_heads', function (Blueprint $table) {
            $table->id();
            $table->string('fee_head_name');
            $table->integer('version_no')->default(0);      //common for all table   
            $table->smallInteger('status')->default(1);     //1-Active, 2-Not Active
            $table->bigInteger('created_by')->nullable();   //common for all table   
            $table->string('ip_address');                   //common for all table   
            $table->text('json_logs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_heads');
    }
};
