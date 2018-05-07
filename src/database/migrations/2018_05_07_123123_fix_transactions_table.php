<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = cp_table_prefix();

        Schema::table($prefix . 'transactions', function (Blueprint $table) {
            $table->renameColumn('amount', 'amount2');
            $table->string('amount1')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $prefix = cp_table_prefix();

        Schema::table($prefix . 'transactions', function (Blueprint $table) {
            $table->dropColumn('amount1');
            $table->renameColumn('amount2', 'amount');
        });
    }
}
