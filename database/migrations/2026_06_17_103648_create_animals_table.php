<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('species');
            $table->string('breed')->nullable();
            $table->enum('gender', ['male', 'female', 'unknown'])->default('unknown');
            $table->integer('age_months')->nullable();
            $table->text('description')->nullable();
            $table->json('photos')->nullable();
            $table->boolean('is_vaccinated')->default(false);
            $table->boolean('is_sterilized')->default(false);
            $table->boolean('is_available')->default(true);
            $table->decimal('price', 10, 2)->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { Schema::dropIfExists('animals'); }
};