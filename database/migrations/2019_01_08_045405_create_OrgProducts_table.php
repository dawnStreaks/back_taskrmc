<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrgProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('OrgProducts', function(Blueprint $table)
		{
			$table->integer('idOrgProducts', true);
			$table->integer('idOrgProductsType')->index('fk_OrgProducts_OrgProductsType1_idx');
			$table->string('OrgProduct', 45);
			$table->timestamps();
			$table->integer('idOrgProdGroup')->index('fk_OrgProducts_OrgProdGroup1_idx');
			$table->primary(['idOrgProducts','idOrgProductsType','idOrgProdGroup']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('OrgProducts');
	}

}
