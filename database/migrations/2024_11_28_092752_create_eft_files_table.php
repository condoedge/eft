<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eft_files', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('file_creation_no')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->date('run_date')->nullable();
            $table->datetime('deposited_at')->nullable();
            $table->tinyInteger('test_file')->nullable();
            $table->tinyInteger('bank_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eft_files');
    }
};
