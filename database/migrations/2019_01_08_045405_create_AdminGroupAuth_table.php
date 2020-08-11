<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdminGroupAuthTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('AdminGroupAuth', function(Blueprint $table)
		{
			$table->integer('idAdminGroupAuth', true);
			$table->integer('idOrgProdGroup')->index('fk_AdminGroupAuth_OrgProdGroup1_idx');
			$table->integer('idOrgAdmin')->index('fk_AdminGroupAuth_OrgAdmin1_idx');
			$table->string('AdminGroupAuth', 45);
			$table->timestamps();
			$table->primary(['idAdminGroupAuth','idOrgProdGroup','idOrgAdmin']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('AdminGroupAuth');
	}

}
