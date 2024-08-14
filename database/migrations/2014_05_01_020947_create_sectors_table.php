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
        Schema::create('sectors', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('city_id');
            $table->float('lat', 16, 10);
            $table->float('lng', 16, 10);
            $table->boolean('blocked')->default(0);
            $table->foreign('city_id')->references('id')
                ->on('cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sectors');
    }
};
