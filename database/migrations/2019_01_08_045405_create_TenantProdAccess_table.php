<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTenantProdAccessTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('TenantProdAccess', function(Blueprint $table)
		{
			$table->integer('idTenantProdAccess', true);
			$table->integer('idTenants')->index('fk_TenantProdAccess_Tenants1_idx');
			$table->integer('idOrgProdGroup')->index('fk_TenantProdAccess_OrgProdGroup1_idx');
			$table->timestamps();
			$table->primary(['idTenantProdAccess','idTenants','idOrgProdGroup']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('TenantProdAccess');
	}

}
