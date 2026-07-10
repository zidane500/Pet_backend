<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // ← Le client qui a passé la commande (compte obligatoire
            // pour commander, mais la consultation du catalogue reste
            // publique).
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('status', [
                'pending',    // en attente de traitement par l'admin
                'confirmed',  // l'admin a contacté le client, confirmé
                'shipped',    // en cours de livraison
                'delivered',  // livré, payé à la livraison
                'cancelled',  // annulée
            ])->default('pending');

            $table->decimal('total_amount', 10, 2);

            // ← Coordonnées de livraison saisies au moment de la commande
            // (on ne se base pas sur le profil utilisateur qui peut
            // changer plus tard : on fige l'info au moment T).
            $table->string('shipping_name');
            $table->string('shipping_phone');
            $table->string('shipping_address');
            $table->string('shipping_city');

            $table->text('notes')->nullable();       // note du client
            $table->text('admin_notes')->nullable();  // note interne admin

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};