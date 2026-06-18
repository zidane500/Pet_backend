<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lost_found', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['lost', 'found']);
            $table->string('animal_name')->nullable();
            $table->string('species');
            $table->string('breed')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->string('last_seen_location');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->date('date_lost_found');
            $table->json('photos')->nullable();
            $table->string('contact_phone')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('lost_found'); }
};