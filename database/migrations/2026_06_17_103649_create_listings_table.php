<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('animal_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['adoption', 'vente', 'perdu', 'trouve', 'accouplement', 'conseils']);
            $table->string('species')->nullable();
            $table->string('breed')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_free')->default(false);
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->json('photos')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->boolean('is_vaccinated')->default(false);
            $table->boolean('is_sterilized')->default(false);
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('views_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { Schema::dropIfExists('listings'); }
};