<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->increments('id');
          //  $table->string('order')->unique();
            $table->string('machine_id');
            $table->string('email');
            $table->integer('itemid')->nullable();
            $table->string('item_name');
            $table->integer('stars');
            $table->boolean('is_Active')->default(false);
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
        Schema::dropIfExists('ratings');
    }
}
