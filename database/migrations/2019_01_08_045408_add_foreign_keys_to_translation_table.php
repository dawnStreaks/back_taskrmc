<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTranslationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('translation', function(Blueprint $table)
		{
			$table->foreign('tenant_id', 'fk_translations_ref_tenants')->references('id')->on('tenant')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('translation', function(Blueprint $table)
		{
			$table->dropForeign('fk_translations_ref_tenants');
		});
	}

}
