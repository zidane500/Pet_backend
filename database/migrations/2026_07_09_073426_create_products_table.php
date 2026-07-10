<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // ← L'admin qui a créé le produit (toujours toi en pratique,
            // mais on garde la traçabilité si jamais un jour il y a
            // plusieurs comptes admin).
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['chat', 'chien', 'oiseau', 'autre']);
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->json('photos')->nullable();

            // ← Permet de masquer un produit (rupture de stock durable,
            // produit retiré) sans le supprimer définitivement.
            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('views_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};