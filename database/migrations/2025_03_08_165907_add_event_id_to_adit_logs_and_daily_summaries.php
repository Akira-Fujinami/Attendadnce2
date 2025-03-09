<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('adit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->after('employee_id');
        });

        Schema::table('daily_summaries', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->after('employee_id');
        });
    }

    public function down()
    {
        Schema::table('adit_logs', function (Blueprint $table) {
            $table->dropColumn('event_id');
        });

        Schema::table('daily_summaries', function (Blueprint $table) {
            $table->dropColumn('event_id');
        });
    }
};

