<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallbackAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = cp_table_prefix();

        Schema::create($prefix . 'callback_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('address', 128);
            $table->string('currency', 10);
            $table->text('pubkey')->nullable();
            $table->string('ipn_url')->nullable();
            $table->string('dest_tag')->nullable();
            $table->unique(['address', 'currency']);
            $table->timestamps();
        });

        Schema::create($prefix . 'deposits', function (Blueprint $table) use ($prefix) {
            $table->bigIncrements('id');

            $table->string('address', 128)->index();
            $table->foreign('address')
                ->references('address')
                ->on($prefix . 'callback_addresses')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->string('txn_id', 128)->unique();
            $table->tinyInteger('status');
            $table->string('status_text');

            $table->string('currency', 10);
            $table->unsignedTinyInteger('confirms');
            $table->string('amount');
            $table->string('amounti');
            $table->string('fee')->nullable();
            $table->string('feei')->nullable();
            $table->string('dest_tag')->nullable();
            $table->timestamps();
        });

        Schema::table($prefix . 'withdrawals', function (Blueprint $table) {
            $table->smallInteger('status')->tinyInteger('status')->change();
            $table->string('amounti')->nullable()->after('amount');
            $table->string('txn_id', 128)->nullable()->unique();
        });

        Schema::table($prefix . 'transactions', function (Blueprint $table) {
            $table->smallInteger('status')->tinyInteger('status')->nullable();
            $table->string('status_text')->nullable();
            $table->string('received_confirms')->nullable();
            $table->string('received_amount')->nullable();
        });

        Schema::table($prefix . 'transfers', function (Blueprint $table) {
            $table->smallInteger('status')->tinyInteger('status')->index()->change();
        });

        Schema::table($prefix . 'ipns', function (Blueprint $table) {
            $table->string('address', 128)->index()->after('ipn_type')->nullable();
            $table->string('amount')->nullable()->after('currency2');
            $table->string('amounti')->nullable()->after('amount');
            $table->string('currency')->nullable()->after('status_text');
            $table->smallInteger('status')->tinyInteger('status')->nullable()->change();
            $table->string('feei')->nullable()->after('fee');
            $table->string('dest_tag')->nullable()->after('feei');
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

        Schema::dropIfExists($prefix . 'deposits');
        Schema::dropIfExists($prefix . 'callback_addresses');
    }
}
