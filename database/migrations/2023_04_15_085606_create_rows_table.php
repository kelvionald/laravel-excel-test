<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRowsTable extends Migration
{
    /**
     * @return void
     */
    public function up(): void
    {
        Schema::create('rows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('rows');
    }
}
