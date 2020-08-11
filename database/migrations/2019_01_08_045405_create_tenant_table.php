<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTenantTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tenant', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('bpm_ref', 45)->nullable()->unique('TenantBPMRef');
			$table->string('trans_file_path', 100)->nullable();
			$table->string('start_date', 45)->nullable();
			$table->string('end_date', 45)->nullable();
			$table->string('created_by', 45)->nullable();
			$table->string('admin', 45)->nullable();
			$table->string('logo_path', 100)->nullable();
			$table->enum('auth_type', array('api','ldap'))->nullable();
			$table->string('default_lang', 2)->nullable();
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tenant');
	}

}
