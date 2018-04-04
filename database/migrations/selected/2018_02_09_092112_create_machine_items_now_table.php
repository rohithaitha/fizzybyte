<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMachineItemsNowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vend_items_now', function (Blueprint $table) {
           $table->increments('id');
            $table->string('machine_id');
            $table->integer('batch_id');
            $table->integer('itemid')->nullable();
            $table->string('item_name');
            $table->double('itemPrice',8,2);
            $table->integer('itemRack');
            $table->integer('quantity');
            $table->string('item_image_path')->nullable();
            $table->integer('freshTimeInHours')->nullable();
            $table->string('description')->nullable();
            $table->string('errormsg')->nullable();            
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
        Schema::dropIfExists('vend_items_now');
    }
}
