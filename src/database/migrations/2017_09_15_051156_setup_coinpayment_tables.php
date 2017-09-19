<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetupCoinpaymentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pre = cp_prefix();

        Schema::create($pre . 'transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('amount');
            $table->string('currency1', 10);
            $table->string('currency2', 10);
            $table->string('address')->nullable();
            $table->string('buyer_email')->nullable();
            $table->string('buyer_name')->nullable();
            $table->string('item_name')->nullable();
            $table->string('item_number')->nullable();
            $table->string('invoice')->nullable();
            $table->text('custom')->nullable();
            $table->string('ipn_url')->nullable();
            $table->string('txn_id')->unique();
            $table->unsignedTinyInteger('confirms_needed');
            $table->unsignedInteger("timeout");
            $table->string('status_url');
            $table->string('qrcode_url');
            $table->timestamps();
        });

        Schema::create($pre . 'transfers', function (Blueprint $table) {

        });

        Schema::create($pre . 'log', function (Blueprint $table) {
           $table->increments('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $pre = cp_prefix();

        Schema::dropIfExists($pre . 'log');
        Schema::dropIfExists($pre . 'transaction');
    }
}
