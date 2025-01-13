<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBeforeAditIdToAditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('adit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('before_adit_id')->nullable()->after('status'); // カラムを追加
            $table->foreign('before_adit_id')->references('id')->on('adit_logs')->onDelete('set null'); // 外部キー制約
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('adit_logs', function (Blueprint $table) {
            $table->dropForeign(['before_adit_id']); // 外部キー制約を削除
            $table->dropColumn('before_adit_id'); // カラムを削除
        });
    }
}
