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
        Schema::create('fee_collections', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('fy_id');
            $table->string('fy_name');
            $table->bigInteger('month_id');
            $table->string('month_name');
            $table->bigInteger('student_id');
            $table->string('admission_no');
            $table->bigInteger('class_id');
            $table->string('class_name');
            $table->string('payment_mode');
            $table->date('payment_date');
            $table->bigInteger('fee_head_type_id')->nullable();
            $table->bigInteger('fee_head_id');
            $table->string('fee_head_name');
            $table->integer('fee_amount');
            $table->integer('received_amount');
            $table->integer('due_amount');
            $table->string('receipt_no');
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
        Schema::dropIfExists('fee_collections');
    }
};
