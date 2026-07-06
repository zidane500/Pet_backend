<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breeders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('verified')->default(false);
            $table->boolean('is_certified')->default(false);
            $table->string('speciality')->nullable(); // ex: "Berger Allemand, Labrador"
            $table->integer('years_experience')->default(0);
            $table->integer('animals_sold_total')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('reviews_count')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breeders');
    }
};