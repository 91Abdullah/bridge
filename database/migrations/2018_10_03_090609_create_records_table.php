<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
    */
    public function up()   
    {
        Schema::create('records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source')->default('');
            $table->string('destination')->default('');
            $table->string('start')->default('');
            $table->string('answer')->default('');
            $table->string('end')->default('');
            $table->string('duration')->default('');
            $table->string('billsec')->default('');
            $table->string('dialstatus')->default('');
            $table->string('bridged_call_id');
            $table->string('incoming_channel_id');
            $table->string('outgoing_channel_id');
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
        Schema::dropIfExists('records');
    }
}
