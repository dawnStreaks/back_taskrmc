<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToFeaturesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Features', function(Blueprint $table)
		{
			$table->foreign('idFeaturesType', 'fk_Features_FeaturesType1')->references('idFeaturesType')->on('FeaturesType')->onUpdate('NO ACTION')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('Features', function(Blueprint $table)
		{
			$table->dropForeign('fk_Features_FeaturesType1');
		});
	}

}
