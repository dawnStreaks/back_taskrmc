<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('email')->unique();
			$table->string('password');
			$table->string('remember_token', 100)->nullable();
			$table->timestamps();
			$table->string('default_password')->nullable();
			$table->integer('status')->default(0);
			$table->string('second_name');
			$table->string('last_name');
			$table->integer('tenant_id')->nullable()->index('fk_users_ref_tenants');
			$table->integer('subtenant_user_group_id')->nullable()->index('fk_users_ref_tenantusergroup');
			$table->string('file_name')->nullable();
			$table->string('original_filename')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
