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
        Schema::create('eft_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eft_file_id')->constrained();
            $table->foreignId('team_id')->nullable()->constrained();
            $table->tinyInteger('status')->nullable();
            $table->string('record', 2000)->nullable();
            $table->decimal('line_amount', 14, 2)->nullable();
            $table->date('line_date')->nullable();
            $table->string('line_slug')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eft_lines');
    }
};
