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
        $prefix = cp_table_prefix();
        
        Schema::create($prefix . 'transactions', function (Blueprint $table) {
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

        Schema::create($prefix . 'transfers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('amount');
            $table->string('currency', 10);
            $table->string('merchant')->nullable();
            $table->string('pbntag')->nullable();
            $table->boolean('auto_confirm')->default(0);
            $table->string('ref_id')->unique();
            $table->unsignedTinyInteger("status");
            $table->timestamps();
        });

        Schema::create($prefix . 'withdrawals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('amount');
            $table->string('currency', 10);
            $table->string('currency2', 10)->nullable();
            $table->string('address')->nullable();
            $table->string('pbntag')->nullable();
            $table->string('dest_tag')->nullable();
            $table->string('ipn_url')->nullable();
            $table->boolean('auto_confirm')->default(0);
            $table->text('note')->nullable();
            $table->string('ref_id')->unique();
            $table->unsignedTinyInteger("status");
            $table->timestamps();
        });

        Schema::create($prefix . 'ipns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });

        Schema::create($prefix . 'log', function (Blueprint $table) {
           $table->increments('id');
           $table->string('type', 32)->nullable();
           $table->text('log');
           $table->string('command', 64);
           $table->string('error')->nullable();
           $table->text('request');
           $table->text('response');
           $table->timestamps();
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

        Schema::dropIfExists($prefix . 'log');
        Schema::dropIfExists($prefix . 'ipns');
        Schema::dropIfExists($prefix . 'transfers');
        Schema::dropIfExists($prefix . 'transaction');
    }
}
