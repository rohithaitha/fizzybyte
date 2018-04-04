<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('RequestResponselogsTab', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->nullable();            
            $table->string('request',4000)->nullable();
            $table->string('response',4000)->nullable();            
            $table->string('errormsg',2000)->nullable();
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
        Schema::dropIfExists('RequestResponselogsTab');
    }
}
