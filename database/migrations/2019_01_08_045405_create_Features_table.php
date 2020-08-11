<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFeaturesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('Features', function(Blueprint $table)
		{
			$table->integer('idFeatures', true);
			$table->string('Feature', 45)->unique('Feature_UNIQUE');
			$table->string('FeatureDescription', 250)->nullable();
			$table->integer('idFeaturesType')->index('fk_Features_FeaturesType1_idx');
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
		Schema::drop('Features');
	}

}
