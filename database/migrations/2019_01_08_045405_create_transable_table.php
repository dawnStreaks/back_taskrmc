<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTransableTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transable', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('table_name');
			$table->string('column_name');
			$table->string('column_type')->nullable();
			$table->string('lang', 2);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('transable');
	}

}
