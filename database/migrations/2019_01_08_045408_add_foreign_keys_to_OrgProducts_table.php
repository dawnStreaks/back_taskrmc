<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOrgProductsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('OrgProducts', function(Blueprint $table)
		{
			$table->foreign('idOrgProdGroup', 'fk_OrgProducts_OrgProdGroup1')->references('idOrgProdGroup')->on('OrgProdGroup')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('idOrgProductsType', 'fk_OrgProducts_OrgProductsType1')->references('idOrgProductsType')->on('OrgProductsType')->onUpdate('NO ACTION')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('OrgProducts', function(Blueprint $table)
		{
			$table->dropForeign('fk_OrgProducts_OrgProdGroup1');
			$table->dropForeign('fk_OrgProducts_OrgProductsType1');
		});
	}

}
