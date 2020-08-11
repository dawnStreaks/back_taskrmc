<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToGroupRulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('GroupRules', function(Blueprint $table)
		{
			$table->foreign('idAdminType', 'fk_GroupRules_AdminType1')->references('idAdminType')->on('AdminType')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('GroupRules', function(Blueprint $table)
		{
			$table->dropForeign('fk_GroupRules_AdminType1');
		});
	}

}
