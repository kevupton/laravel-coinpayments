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
            $table->string('address', 128)->unique();
            $table->string('currency', 10);
            $table->text('pubkey')->nullable();
            $table->string('ipn_url')->nullable();
            $table->string('dest_tag')->nullable();
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
            $table->smallInteger('status')->change();
            $table->string('amounti')->nullable()->after('amount');
            $table->string('txn_id', 128)->nullable()->unique();
        });

        Schema::table($prefix . 'transactions', function (Blueprint $table) {
            $table->smallInteger('status')->nullable();
            $table->string('status_text')->nullable();
            $table->string('received_confirms')->nullable();
            $table->string('received_amount')->nullable();
        });

        Schema::table($prefix . 'transfers', function (Blueprint $table) {
            $table->smallInteger('status')->index()->change();
        });

        Schema::table($prefix . 'ipns', function (Blueprint $table) {
            $table->string('address', 128)->index()->after('ipn_type')->nullable();
            $table->string('amount')->nullable()->after('currency2');
            $table->string('amounti')->nullable()->after('amount');
            $table->string('currency')->nullable()->after('status_text');
            $table->unsignedTinyInteger('confirms')->nullable()->after('currency2');
            $table->smallInteger('status')->nullable()->change();
            $table->string('feei')->nullable()->after('fee');
            $table->string('dest_tag')->nullable()->after('feei');

            // change existing to nullable
            $table->string('ipn_type', 32)->nullable()->change();
            $table->string('txn_id')->nullable()->change();
            $table->string('status_text')->nullable()->change();
            $table->string('currency1')->nullable()->change();
            $table->string('currency2')->nullable()->change();
            $table->string('amount1')->nullable()->change();
            $table->string('amount2')->nullable()->change();
            $table->string('fee')->nullable()->change();
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

        Schema::table($prefix . 'ipns', function (Blueprint $table) {
            $table->dropIndex(['address']);
            $table->dropColumn('address');
            $table->dropColumn('amount');
            $table->dropColumn('amounti');
            $table->dropColumn('confirms');
            $table->dropColumn('currency');
            $table->dropColumn('feei');
            $table->dropColumn('dest_tag');
        });

        Schema::table($prefix . 'transactions', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('status_text');
            $table->dropColumn('received_confirms');
            $table->dropColumn('received_amount');
        });

        Schema::table($prefix . 'withdrawals', function (Blueprint $table) {
            $table->dropColumn('amounti');
            $table->dropUnique(['txn_id']);
            $table->dropColumn('txn_id');
        });

        Schema::table($prefix . 'transfers', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::dropIfExists($prefix . 'deposits');
        Schema::dropIfExists($prefix . 'callback_addresses');
    }
}
