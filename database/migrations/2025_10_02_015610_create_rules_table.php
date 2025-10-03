<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('discount_value', 8, 2);
            $table->enum('discount_type', ['percentage', 'fixed_amount']);
            $table->enum('discount_base', ['current_price', 'compare_at_price']);
            $table->enum('apply_to_type', ['products', 'collections', 'tags', 'vendors', 'whole_store']);
            $table->json('apply_to_targets')->nullable(); // ví dụ: ["gid://shopify/Product/123", ...] hoặc ["tag1", "tag2"]
            $table->json('exclude_products')->nullable(); // ["gid://shopify/Product/123", ...]
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->json('tags_to_add')->nullable(); // ["sale", "discount"]
            $table->boolean('active')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
