<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFeaturesGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('FeaturesGroup', function(Blueprint $table)
		{
			$table->integer('idFeaturesGroup', true);
			$table->integer('idFeatures')->index('fk_FeaturesGroup_Features1_idx');
			$table->string('FeaturesGroup', 45);
			$table->string('FeaturesGroupDesc', 100)->nullable();
			$table->timestamps();
			$table->primary(['idFeaturesGroup','idFeatures']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('FeaturesGroup');
	}

}
