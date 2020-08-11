<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserVacationRequestTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('userVacationRequest', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('user_id');
			$table->integer('tenant_id');
			$table->text('userData', 65535);
			$table->date('from_date');
			$table->date('to_date');
			$table->enum('status', array('pending','approved','rejected',''));
			$table->integer('reviewer_id')->nullable();
			$table->text('reviewerData', 65535)->nullable();
			$table->text('reviewComment', 65535)->nullable();
			$table->enum('vacationType', array('sick','annual','urgent',''));
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
		Schema::drop('userVacationRequest');
	}

}
