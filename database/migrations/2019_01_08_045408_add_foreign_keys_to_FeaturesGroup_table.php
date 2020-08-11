<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToFeaturesGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('FeaturesGroup', function(Blueprint $table)
		{
			$table->foreign('idFeatures', 'fk_FeaturesGroup_Features1')->references('idFeatures')->on('Features')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('FeaturesGroup', function(Blueprint $table)
		{
			$table->dropForeign('fk_FeaturesGroup_Features1');
		});
	}

}
