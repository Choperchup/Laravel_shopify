<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            // Kiểm tra trước khi thêm cột 'is_enabled'
            if (!Schema::hasColumn('rules', 'is_enabled')) {
                $table->boolean('is_enabled')->default(false)->after('tags_to_add');
            }

            // Kiểm tra trước khi thêm cột 'status'
            if (!Schema::hasColumn('rules', 'status')) {
                $table->string('status')->default('INACTIVE')->after('is_enabled');
            }

            // Kiểm tra trước khi thêm các cột theo dõi tiến độ
            if (!Schema::hasColumn('rules', 'processed_products')) {
                $table->unsignedInteger('processed_products')->default(0)->after('status');
            }
            if (!Schema::hasColumn('rules', 'total_products')) {
                $table->unsignedInteger('total_products')->default(0)->after('processed_products');
            }
            if (!Schema::hasColumn('rules', 'job_batch_id')) {
                $table->string('job_batch_id')->nullable()->after('total_products');
            }
            if (!Schema::hasColumn('rules', 'activated_at')) {
                $table->timestamp('activated_at')->nullable()->after('end_at');
            }

            // Kiểm tra xem cột `active` có tồn tại không rồi mới xóa
            if (Schema::hasColumn('rules', 'active')) {
                $table->dropColumn('active');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('rules', function (Blueprint $table) {
            // Kiểm tra xem cột 'active' có tồn tại không, nếu chưa thì thêm lại
            if (!Schema::hasColumn('rules', 'active')) {
                $table->boolean('active')->default(false)->after('tags_to_add');
            }

            // Mảng các cột cần xóa
            $columnsToDrop = [
                'is_enabled',
                'status',
                'processed_products',
                'total_products',
                'job_batch_id',
                'activated_at'
            ];

            // Vòng lặp để kiểm tra và xóa từng cột
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('rules', 'column_name')) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
