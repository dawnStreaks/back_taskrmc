<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSubtenantTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('subtenant_type', function(Blueprint $table)
		{
			$table->foreign('tenant_id', 'fk_SubTenantType_Tenants1')->references('id')->on('tenant')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('subtenant_type', function(Blueprint $table)
		{
			$table->dropForeign('fk_SubTenantType_Tenants1');
		});
	}

}
