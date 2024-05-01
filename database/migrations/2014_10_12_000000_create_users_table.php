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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('sector_id')->nullable()->default(null);
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('city_id')->nullable()->default(null);;
            $table->string('email', 50)->unique();
            $table->date('birth_date');
            $table->string('password')->unique();
            $table->enum('gender', ['male', 'female']);
            $table->string('phone_no')->unique();
            $table->string('img_url')->nullable()->default(null);
            $table->integer('badget')->nullable()->default(0);
            $table->boolean('blocked')->default(0);
            $table->timestamps();
            $table->foreign('sector_id')->references('id')
                ->on('sectors')->onDelete('cascade');
            $table->foreign('role_id')->references('id')
                ->on('roles')->onDelete('cascade');
            $table->foreign('city_id')->references('id')
                ->on('cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
