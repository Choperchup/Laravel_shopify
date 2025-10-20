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
        Schema::table('rules', function (Blueprint $table) {
            // Cột status sẽ thay thế cho cột 'active' (boolean) cũ
            $table->string('status')->default('INACTIVE')->after('tags_to_add');

            // Số sản phẩm đã xử lý (X)
            $table->unsignedInteger('processed_products')->default(0)->after('status');

            // Tổng số sản phẩm (Y)
            $table->unsignedInteger('total_products')->default(0)->after('processed_products');

            // ID của batch job đang chạy, để theo dõi tiến độ
            $table->string('job_batch_id')->nullable()->after('total_products');

            // Thời gian hoàn tất kích hoạt
            $table->timestamp('activated_at')->nullable()->after('end_at');

            $table->dropColumn('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            $table->dropColumn(['status', 'processed_products', 'total_products', 'job_batch_id', 'activated_at']);
            $table->boolean('active')->default(false);
        });
    }
};
