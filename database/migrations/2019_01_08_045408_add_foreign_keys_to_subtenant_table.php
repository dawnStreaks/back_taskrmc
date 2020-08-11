<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSubtenantTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('subtenant', function(Blueprint $table)
		{
			$table->foreign('subtenant_type_id', 'fk_SubTenant_SubTenantType1')->references('id')->on('subtenant_type')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('tenant_id', 'fk_SubTenant_Tenants1')->references('id')->on('tenant')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('subtenant', function(Blueprint $table)
		{
			$table->dropForeign('fk_SubTenant_SubTenantType1');
			$table->dropForeign('fk_SubTenant_Tenants1');
		});
	}

}
