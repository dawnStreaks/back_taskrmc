<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGroupRulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('GroupRules', function(Blueprint $table)
		{
			$table->integer('idGroupRules', true);
			$table->integer('idAdminType')->index('fk_GroupRules_AdminType1_idx');
			$table->string('GroupRule', 45);
			$table->timestamps();
			$table->primary(['idGroupRules','idAdminType']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('GroupRules');
	}

}
