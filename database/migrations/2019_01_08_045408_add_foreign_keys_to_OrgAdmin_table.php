<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOrgAdminTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('OrgAdmin', function(Blueprint $table)
		{
			$table->foreign('idAdminType', 'fk_OrgAdmin_AdminType1')->references('idAdminType')->on('AdminType')->onUpdate('NO ACTION')->onDelete('CASCADE');
			$table->foreign('idOrganization', 'fk_OrgAdmin_Organization')->references('idOrganization')->on('Organization')->onUpdate('NO ACTION')->onDelete('CASCADE');
			$table->foreign('user_id', 'fk_OrgAdmin_users1')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('OrgAdmin', function(Blueprint $table)
		{
			$table->dropForeign('fk_OrgAdmin_AdminType1');
			$table->dropForeign('fk_OrgAdmin_Organization');
			$table->dropForeign('fk_OrgAdmin_users1');
		});
	}

}
