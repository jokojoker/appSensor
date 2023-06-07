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
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->integer('reading_id_from_sensor')->nullable();
            $table->integer('sensor_id')->index();
            $table->dateTime('reading_dt')->nullable();
            $table->decimal('temperature',4,3)->index();
            $table->integer('reading_type')->nullable()->index();// 1 - Celsius, 2 - Farenheit
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
