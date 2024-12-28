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
        Schema::create('employees', function (Blueprint $table) {
            $table->id(); // 自動インクリメントの主キー
            $table->unsignedBigInteger('company_id'); // 会社ID（外部キー）
            $table->string('name'); // 名前
            $table->string('email')->unique(); // メールアドレス
            $table->string('password'); // パスワード
            $table->enum('role', ['staff', 'admin'])->default('staff'); // ロール
            $table->integer('hourly_wage')->nullable(); // 時給
            $table->integer('transportation_fee')->nullable(); // 交通費
            $table->enum('retired', ['在職中', '休職中', '退職済み'])->default('在職中'); // 在職
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
