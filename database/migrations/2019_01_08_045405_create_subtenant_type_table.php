<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubtenantTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subtenant_type', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('name', 45)->nullable()->unique('SubTenantType_UNIQUE');
			$table->string('description', 128)->nullable();
			$table->timestamps();
			$table->integer('tenant_id')->index('fk_SubTenantType_Tenants1_idx');
			$table->primary(['id','tenant_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subtenant_type');
	}

}
