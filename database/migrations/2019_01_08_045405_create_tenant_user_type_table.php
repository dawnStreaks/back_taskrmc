<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTenantUserTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tenant_user_type', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 45)->nullable();
			$table->string('description', 45)->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->integer('tenant_id')->nullable()->index('fk_TenantUsersType_Tenants1_idx1');
			$table->integer('level');
			$table->text('default_permission', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tenant_user_type');
	}

}
