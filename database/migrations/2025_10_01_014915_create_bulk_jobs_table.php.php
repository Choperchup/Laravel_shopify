<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bulk_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('pending'); // pending, processing, done, failed
            $table->string('action');                     // status, add_tags, remove_tags, add_collection, remove_collection
            $table->json('product_ids');
            $table->json('payload')->nullable();
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_jobs');
    }
};