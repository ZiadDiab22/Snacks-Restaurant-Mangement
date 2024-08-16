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
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('delivery_emp_id')->nullable()->default(null);
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('emp_id')->nullable()->default(null);
            $table->unsignedInteger('sector_id');
            $table->unsignedInteger('status_id');
            $table->float('lat', 16, 10);
            $table->float('lng', 16, 10);
            $table->float('distance');
            $table->float('delivery_price');
            $table->float('order_price');
            $table->float('total_price');
            $table->timestamps();
            $table->foreign('delivery_emp_id')->references('id')
                ->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')
                ->on('users')->onDelete('cascade');
            $table->foreign('emp_id')->references('id')
                ->on('users')->onDelete('cascade');
            $table->foreign('sector_id')->references('id')
                ->on('sectors')->onDelete('cascade');
            $table->foreign('status_id')->references('id')
                ->on('order_statuses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
