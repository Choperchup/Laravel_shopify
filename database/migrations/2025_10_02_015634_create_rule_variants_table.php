<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rule_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained()->onDelete('cascade');
            $table->string('variant_id'); // ví dụ: "gid://shopify/ProductVariant/123"
            $table->string('product_id'); // ví dụ: "gid://shopify/Product/123"
            $table->decimal('original_price', 15, 4)->nullable();
            $table->decimal('original_compare_at_price', 15, 4)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('rule_variants');
    }
};
