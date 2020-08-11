<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAdminGroupAuthTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('AdminGroupAuth', function(Blueprint $table)
		{
			$table->foreign('idOrgAdmin', 'fk_AdminGroupAuth_OrgAdmin1')->references('idOrgAdmin')->on('OrgAdmin')->onUpdate('NO ACTION')->onDelete('CASCADE');
			$table->foreign('idOrgProdGroup', 'fk_AdminGroupAuth_OrgProdGroup1')->references('idOrgProdGroup')->on('OrgProdGroup')->onUpdate('NO ACTION')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('AdminGroupAuth', function(Blueprint $table)
		{
			$table->dropForeign('fk_AdminGroupAuth_OrgAdmin1');
			$table->dropForeign('fk_AdminGroupAuth_OrgProdGroup1');
		});
	}

}
