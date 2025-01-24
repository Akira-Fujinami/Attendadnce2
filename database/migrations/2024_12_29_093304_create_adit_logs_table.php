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
        Schema::create('adit_logs', function (Blueprint $table) {
            $table->id(); // 自動インクリメントの主キー
            $table->unsignedBigInteger('company_id'); // 会社ID（外部キー）
            $table->unsignedBigInteger('employee_id'); // 従業員ID（外部キー）
            $table->date('date'); // 勤怠日
            $table->datetime('minutes'); // 打刻分
            $table->enum('adit_item', ['work_start', 'break_start', 'break_end', 'work_end']);
            $table->enum('status', ['pending', 'approved', 'rejected']); // 状態
            $table->unsignedBigInteger('before_adit_id')->nullable();
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adit_logs');
    }
};
