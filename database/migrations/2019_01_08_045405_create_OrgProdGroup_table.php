<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrgProdGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('OrgProdGroup', function(Blueprint $table)
		{
			$table->integer('idOrgProdGroup', true);
			$table->string('ProdGroup', 45);
			$table->string('ProdGroupDescription', 100)->nullable();
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
		Schema::drop('OrgProdGroup');
	}

}
