<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTenantProdAccessTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('TenantProdAccess', function(Blueprint $table)
		{
			$table->foreign('idOrgProdGroup', 'fk_TenantProdAccess_OrgProdGroup1')->references('idOrgProdGroup')->on('OrgProdGroup')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('idTenants', 'fk_TenantProdAccess_Tenants1')->references('id')->on('tenant')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('TenantProdAccess', function(Blueprint $table)
		{
			$table->dropForeign('fk_TenantProdAccess_OrgProdGroup1');
			$table->dropForeign('fk_TenantProdAccess_Tenants1');
		});
	}

}
