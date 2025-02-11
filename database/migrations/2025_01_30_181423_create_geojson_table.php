<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Explicitly use PostgreSQL
        Schema::connection('pgsql')->create('geojson_data', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->jsonb('geojson'); // jsonb is optimized for GeoJSON storage
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('pgsql')->dropIfExists('geojson_data');
    }
};

