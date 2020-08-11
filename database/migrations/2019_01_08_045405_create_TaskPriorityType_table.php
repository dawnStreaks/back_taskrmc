<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaskPriorityTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('TaskPriorityType', function(Blueprint $table)
		{
			$table->increments('idTaskPriorityType');
			$table->integer('TypeCodeMin');
			$table->integer('TypeCodeMax');
			$table->integer('PRCType')->unsigned()->index('taskprioritytype_prctype_index');
			$table->timestamps();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('TaskPriorityType');
	}

}
