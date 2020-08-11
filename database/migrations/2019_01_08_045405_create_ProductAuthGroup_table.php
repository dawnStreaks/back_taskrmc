<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProductAuthGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ProductAuthGroup', function(Blueprint $table)
		{
			$table->integer('idProductAuthGroup', true);
			$table->integer('idOrgProducts')->index('fk_ProductAuthGroup_OrgProducts1_idx');
			$table->integer('idFeaturesGroup')->index('fk_ProductAuthGroup_FeaturesGroup1_idx');
			$table->string('ProductAuthGroup', 45);
			$table->timestamps();
			$table->primary(['idProductAuthGroup','idOrgProducts','idFeaturesGroup']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ProductAuthGroup');
	}

}
