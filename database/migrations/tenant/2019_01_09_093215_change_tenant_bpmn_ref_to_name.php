<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTenantBpmnRefToName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant', function(Blueprint $table)
        {
            $table->dropColumn('bpm_ref');
        });

        Schema::table('tenant', function(Blueprint $table)
        {
            $table->string('name', 45);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenant', function(Blueprint $table)
        {
            $table->dropColumn('name');
        });

        Schema::table('tenant', function(Blueprint $table)
        {
            $table->string('bpm_ref', 45);
        });
    }
}
