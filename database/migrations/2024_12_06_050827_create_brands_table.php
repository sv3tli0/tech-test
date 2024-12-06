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
        Schema::create('brands', function (Blueprint $table) {
            $table->bigIncrements('brand_id')->primary();
            // Brand name required - up to 100 varchar length.
            $table->string('brand_name', 100)
                ->nullable(false);
            // Image Url/string - potentially can be null.
            $table->string('brand_image', 200)
                ->nullable();
            $table->unsignedTinyInteger('rating')
                ->nullable(false)
                ->default(0)
                ->index(); // set index in case of queries toward specific rating value.
            // Keep the timestamps as it is good to have datetime track over objects.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
