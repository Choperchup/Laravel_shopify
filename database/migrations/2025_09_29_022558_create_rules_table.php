<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('discount_value', 8, 2);
            $table->enum('discount_type', ['percentage', 'fix_amount']);
            $table->enum('discount_on', ['current_price', 'compare_at_price']);
            $table->enum('apply_to', ['product_variant', 'tag', 'vendor', 'collection', 'whole_store']);
            $table->json('targets')->nullable(); // Lưu array IDs hoặc values
            $table->text('summary')->nullable();
            $table->string('add_tag')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('excluded_count')->default(0);
            $table->json('excluded_ids')->nullable(); // Array product IDs excluded
            $table->enum('status', ['on', 'off'])->default('off');
            $table->boolean('archived')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
