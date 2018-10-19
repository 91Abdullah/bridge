<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBranchNameColumnInPinCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pin_codes', function (Blueprint $table) {
            $table->string('branch_name')->after('code')->default('JSBank');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pin_codes', function (Blueprint $table) {
            $table->dropColumn('branch_code');
        });
    }
}
