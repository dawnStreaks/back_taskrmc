<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubtenantTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subtenant', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('tenant_id')->nullable()->index('fk_SubTenant_Tenants1_idx');
			$table->string('name', 45)->nullable();
			$table->text('description', 65535)->nullable();
			$table->integer('subtenant_type_id')->nullable()->default(1)->index('fk_SubTenant_SubTenantType1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('parent_id')->nullable()->default(0);
			$table->integer('bpm_ref')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subtenant');
	}

}
