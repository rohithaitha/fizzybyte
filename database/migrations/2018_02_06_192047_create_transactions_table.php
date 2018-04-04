<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order')->unique();
            $table->string('machine_id');
            $table->string('email');
            $table->integer('itemid')->nullable();
            $table->string('item_name');    
            $table->string('item_image_path')->nullable();    
            $table->integer('itemrack');
            $table->double('txnamount',3,2);            
            $table->double('discount',3,2)->default(0.0);
            $table->string('txnstatus');
            $table->string('request')->nullable();
            $table->string('errormsg',1000)->nullable();
            $table->string('comments',1000)->nullable();
            $table->integer('stars')->nullable();
            $table->string('couponcode')->nullable();
            $table->boolean('is_Active')->default(true);
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
        Schema::dropIfExists('transactions');
    }
}
