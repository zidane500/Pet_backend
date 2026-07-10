<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // ← nullOnDelete : si un produit est supprimé plus tard, on
            // garde quand même l'historique de la commande (on a déjà
            // figé son nom/prix ci-dessous au moment de l'achat).
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // ← Copie du nom et du prix au moment de la commande. Si
            // l'admin change le prix du produit après coup, ça ne doit
            // JAMAIS modifier les commandes déjà passées.
            $table->string('product_name');
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};