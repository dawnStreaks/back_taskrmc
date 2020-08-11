<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSubtenantUserGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('subtenant_user_group', function(Blueprint $table)
		{
			$table->foreign('subtenant_id', 'fk_TenantAdminGroup_SubTenant1')->references('id')->on('subtenant')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('tenant_id', 'fk_TenantAdminGroup_Tenants1')->references('id')->on('tenant')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('tenant_user_type_id', 'fk_TenantUserGroup_TenantUsersType1')->references('id')->on('tenant_user_type')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('subtenant_user_group', function(Blueprint $table)
		{
			$table->dropForeign('fk_TenantAdminGroup_SubTenant1');
			$table->dropForeign('fk_TenantAdminGroup_Tenants1');
			$table->dropForeign('fk_TenantUserGroup_TenantUsersType1');
		});
	}

}
