<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('AdminType', function(Blueprint $table)
		{
			$table->integer('idAdminType', true);
			$table->string('TypeCode', 21)->unique('TypeCode_UNIQUE');
			$table->string('Type', 45)->unique('Type_UNIQUE');
			$table->string('Description', 100)->nullable();
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
		Schema::drop('AdminType');
	}

}
