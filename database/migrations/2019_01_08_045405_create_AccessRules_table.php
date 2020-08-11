<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAccessRulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('AccessRules', function(Blueprint $table)
		{
			$table->integer('idAccessRules', true);
			$table->integer('GroupRules_idGroupRules')->index('fk_AccessRules_GroupRules1_idx');
			$table->string('Rule', 45);
			$table->timestamps();
			$table->primary(['idAccessRules','GroupRules_idGroupRules']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('AccessRules');
	}

}
