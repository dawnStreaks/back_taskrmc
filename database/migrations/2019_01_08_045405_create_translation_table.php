<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTranslationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('translation', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('table_name');
			$table->string('column_name');
			$table->integer('foreign_key')->unsigned();
			$table->string('locale');
			$table->text('value', 65535)->nullable();
			$table->timestamps();
			$table->integer('tenant_id')->nullable()->index('fk_translations_ref_tenants');
			$table->unique(['table_name','column_name','foreign_key','locale'], 'translations_table_name_column_name_foreign_key_locale_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('translation');
	}

}
