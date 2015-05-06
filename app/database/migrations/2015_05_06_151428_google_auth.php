<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GoogleAuth extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function($table)
        {
             // adding title
            $table->string('googleSpreadsheetUserId')->nullable();
            $table->string('googleSpreadsheetRefreshToken')->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function($table)
        {
            // dropping column
            $table->dropColumn('googleSpreadsheetUserId');
            $table->dropColumn('googleSpreadsheetRefreshToken');
        });
	}

}
