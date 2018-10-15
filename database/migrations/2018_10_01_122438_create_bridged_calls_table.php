<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBridgedCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bridged_calls', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('bridge_class');
            $table->string('bridge_type');
            $table->string('creator');
            $table->string('channels');
            $table->string('name');  
            $table->string('technology');  
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
        Schema::dropIfExists('bridged_calls');
    }
}
