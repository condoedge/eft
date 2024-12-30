<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eft_lines', function (Blueprint $table) {
            $table->tinyInteger('caused_error')->nullable();
            $table->string('error_reason')->nullable();
            $table->nullableMorphs('counterpartyable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eft_lines', function (Blueprint $table) {
            
        });
    }
};