<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->foreign('tenant_id', 'fk_users_ref_tenants')->references('id')->on('tenant')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('subtenant_user_group_id', 'fk_users_ref_tenantusergroup')->references('id')->on('subtenant_user_group')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropForeign('fk_users_ref_tenants');
			$table->dropForeign('fk_users_ref_tenantusergroup');
		});
	}

}
