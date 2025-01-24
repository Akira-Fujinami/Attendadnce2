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
        Schema::create('events', function (Blueprint $table) {
            $table->id(); // イベントID
            $table->unsignedBigInteger('company_id');
            $table->string('name'); // イベント名
            $table->date('fromDate'); // イベントの日付
            $table->date('toDate'); // イベントの日付
            $table->text('description')->nullable(); // イベントの説明
            $table->timestamps(); // 作成日時・更新日時
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
