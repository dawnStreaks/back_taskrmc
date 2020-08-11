<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrgAdminTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('OrgAdmin', function(Blueprint $table)
		{
			$table->integer('idOrgAdmin', true);
			$table->integer('idOrganization')->index('fk_OrgAdmin_Organization_idx');
			$table->integer('idAdminType')->index('fk_OrgAdmin_AdminType1_idx');
			$table->string('AdminType', 45);
			$table->date('BirthDate');
			$table->string('AdminPicPath', 128)->nullable();
			$table->timestamps();
			$table->integer('user_id')->unsigned()->index('fk_OrgAdmin_users1_idx');
			$table->primary(['idOrgAdmin','idOrganization','idAdminType','user_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('OrgAdmin');
	}

}
