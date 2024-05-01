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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('emp_id');
            $table->unsignedInteger('city_id');
            $table->unsignedInteger('status_id');
            $table->string('addresse');
            $table->foreign('city_id')->references('id')
                ->on('cities')->onDelete('cascade');
            $table->foreign('emp_id')->references('id')
                ->on('users')->onDelete('cascade');
            $table->foreign('status_id')->references('id')
                ->on('order_statuses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
