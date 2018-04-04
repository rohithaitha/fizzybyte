<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('txnid')->unique();
            $table->string('status');
            $table->string('transaction_fee');
            $table->string('amount');
            $table->string('discount');
            $table->string('firstname');
            $table->string('udf1')->nullable();
            $table->string('udf2')->nullable();
            $table->string('udf3')->nullable();
            $table->string('udf4')->nullable();
            $table->string('udf5')->nullable();
            $table->string('email');
            $table->string('request',4000)->nullable();
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
        Schema::dropIfExists('receipts');
    }
}
