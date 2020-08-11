<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFeaturesTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('FeaturesType', function(Blueprint $table)
		{
			$table->integer('idFeaturesType', true);
			$table->string('FeatureType', 45)->unique('FeatureType_UNIQUE');
			$table->string('TypeDescription', 45)->unique('TypeDescription_UNIQUE');
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
		Schema::drop('FeaturesType');
	}

}
