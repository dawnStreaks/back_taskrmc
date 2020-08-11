<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAccessRulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('AccessRules', function(Blueprint $table)
		{
			$table->foreign('GroupRules_idGroupRules', 'fk_AccessRules_GroupRules1')->references('idGroupRules')->on('GroupRules')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('AccessRules', function(Blueprint $table)
		{
			$table->dropForeign('fk_AccessRules_GroupRules1');
		});
	}

}
