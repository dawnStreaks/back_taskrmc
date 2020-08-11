<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTaskPriorityTypeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('TaskPriorityType', function(Blueprint $table)
		{
			$table->foreign('PRCType', 'fk_TaskPriority_PRCType')->references('IdPRCType')->on('PRCType')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('TaskPriorityType', function(Blueprint $table)
		{
			$table->dropForeign('fk_TaskPriority_PRCType');
		});
	}

}
