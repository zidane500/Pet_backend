<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('promotion_price', 10, 2)->nullable()->after('price');
            $table->timestamp('promotion_ends_at')->nullable()->after('promotion_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['promotion_price', 'promotion_ends_at']);
        });
    }
};