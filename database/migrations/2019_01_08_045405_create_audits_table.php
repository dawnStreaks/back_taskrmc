<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAuditsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('audits', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->nullable();
			$table->string('user_type')->nullable();
			$table->string('event');
			$table->integer('auditable_id')->unsigned();
			$table->string('auditable_type');
			$table->text('old_values', 65535)->nullable();
			$table->text('new_values', 65535)->nullable();
			$table->text('url', 65535)->nullable();
			$table->string('ip_address', 45)->nullable();
			$table->string('user_agent')->nullable();
			$table->string('tags')->nullable();
			$table->timestamps();
			$table->index(['user_id','user_type']);
			$table->index(['auditable_id','auditable_type']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('audits');
	}

}
