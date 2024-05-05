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
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('type_id');
            $table->string('disc')->nullable()->default(null);
            $table->integer('price');
            $table->integer('quantity');
            $table->float('discount_rate')->nullable()->default(null);
            $table->integer('likes')->default(0);
            $table->string('img_url')->nullable()->default(null);
            $table->boolean('visible')->default(1);
            $table->foreign('type_id')->references('id')
                ->on('products_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
