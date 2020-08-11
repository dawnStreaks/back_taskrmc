<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToProductAuthGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ProductAuthGroup', function(Blueprint $table)
		{
			$table->foreign('idFeaturesGroup', 'fk_ProductAuthGroup_FeaturesGroup1')->references('idFeaturesGroup')->on('FeaturesGroup')->onUpdate('NO ACTION')->onDelete('CASCADE');
			$table->foreign('idOrgProducts', 'fk_ProductAuthGroup_OrgProducts1')->references('idOrgProducts')->on('OrgProducts')->onUpdate('NO ACTION')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ProductAuthGroup', function(Blueprint $table)
		{
			$table->dropForeign('fk_ProductAuthGroup_FeaturesGroup1');
			$table->dropForeign('fk_ProductAuthGroup_OrgProducts1');
		});
	}

}
