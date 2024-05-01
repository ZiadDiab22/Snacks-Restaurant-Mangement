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
            $table->string('disc');
            $table->integer('price');
            $table->integer('quantity');
            $table->integer('source_price');
            $table->float('discount_rate');
            $table->integer('likes');
            $table->string('img_url')->nullable()->default(null);;
            $table->boolean('visible')->nullable()->default(1);
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
