<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrgProductsTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('OrgProductsType', function(Blueprint $table)
		{
			$table->integer('idOrgProductsType', true);
			$table->string('ProductType', 45)->unique('ProductType_UNIQUE');
			$table->string('ProductDescription', 128)->nullable();
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
		Schema::drop('OrgProductsType');
	}

}
