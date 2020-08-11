<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTenantUserTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tenant_user_type', function(Blueprint $table)
		{
			$table->foreign('tenant_id', 'fk_TenantUsersType_Tenants1')->references('id')->on('tenant')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tenant_user_type', function(Blueprint $table)
		{
			$table->dropForeign('fk_TenantUsersType_Tenants1');
		});
	}

}
