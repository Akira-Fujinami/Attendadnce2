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
        Schema::create('daily_summaries', function (Blueprint $table) {
            $table->id(); // プライマリキー
            $table->unsignedBigInteger('company_id'); // 従業員ID（外部キー）
            $table->unsignedBigInteger('employee_id'); // 従業員ID（外部キー）
            $table->date('date'); // 日付
            $table->decimal('total_work_hours', 5, 2)->nullable(); // 総労働時間
            $table->decimal('total_break_hours', 5, 2)->nullable(); // 総休憩時間
            $table->decimal('overtime_hours', 5, 2)->nullable(); // 残業時間
            $table->decimal('salary', 10, 2)->nullable(); // 給与
            $table->text('error_types')->nullable(); // エラー種別（カンマ区切り）
            $table->timestamps(); // 作成日時、更新日時
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_summaries');
    }
};
