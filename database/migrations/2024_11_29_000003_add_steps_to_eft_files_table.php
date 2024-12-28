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
        Schema::table('eft_files', function (Blueprint $table) {
            $table->datetime('rejected_at')->nullable();
            $table->datetime('accepted_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->tinyInteger('completed_portion')->nullable();
            $table->datetime('completed_date')->nullable();
            $table->decimal('completed_amount', 14, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eft_files', function (Blueprint $table) {
            
        });
    }
};