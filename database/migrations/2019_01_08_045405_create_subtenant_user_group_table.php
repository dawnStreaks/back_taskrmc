<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubtenantUserGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subtenant_user_group', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name');
			$table->text('description', 65535)->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->integer('tenant_id')->nullable()->index('fk_TenantAdminGroup_Tenants1_idx');
			$table->integer('subtenant_id')->nullable()->index('fk_TenantAdminGroup_SubTenant1_idx');
			$table->integer('tenant_user_type_id')->nullable()->index('fk_TenantUserGroup_TenantUsersType1_idx');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subtenant_user_group');
	}

}
