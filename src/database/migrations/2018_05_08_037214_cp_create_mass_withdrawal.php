<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CpCreateMassWithdrawal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up ()
    {
        $prefix = cp_table_prefix();

        Schema::create($prefix . 'mass_withdrawals', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::create($prefix . 'conversions', function (Blueprint $table) {
            $table->increments('id');

            $table->string('amount');
            $table->string('ref_id', 32)->unique();
            $table->string('from', 10)->index();
            $table->string('to', 10)->index();
            $table->string('address')->nullable();
            $table->string('dest_tag')->nullable();

            $table->timestamps();
        });

        Schema::table($prefix . 'withdrawals', function (Blueprint $table) use ($prefix) {
            $table->string('amount2')->nullable()->after('amount');
            $table->string('status_text')->nullable()->after('status');
            $table->unsignedInteger('mass_withdrawal_id')->nullable()->index()->after('id');
            $table->foreign('mass_withdrawal_id')
                ->references('id')
                ->on($prefix . 'mass_withdrawals')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::table($prefix . 'transactions', function (Blueprint $table) {
           $table->string('fee')->nullable()->after('currency2');
        });

        Schema::table($prefix . 'ipns', function (Blueprint $table) {
           $table->string('ref_id')->nullable()->after('ipn_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down ()
    {
        $prefix = cp_table_prefix();

        try {
            Schema::table($prefix . 'ipns', function (Blueprint $table) use ($prefix) {
                $table->dropColumn('ref_id');
            });
        } catch (Exception $e) {
        }

        try {
            Schema::table($prefix . 'transactions', function (Blueprint $table) use ($prefix) {
                $table->dropColumn('fee');
            });
        } catch (Exception $e) {
        }

        try {
            Schema::table($prefix . 'withdrawals', function (Blueprint $table) use ($prefix) {
                $table->dropForeign(['mass_withdrawal_id']);
                $table->dropIndex(['mass_withdrawal_id']);
                $table->dropColumn('mass_withdrawal_id');
                $table->dropColumn('status_text');
                $table->dropColumn('amount2');
            });
        } catch (Exception $e) {
        }

        Schema::dropIfExists($prefix . 'conversions');
        Schema::dropIfExists($prefix . 'mass_withdrawals');
    }
}
