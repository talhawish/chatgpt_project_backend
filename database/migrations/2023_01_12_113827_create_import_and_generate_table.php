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
        Schema::create('import_and_generate', function (Blueprint $table) {
            $table->id();
            $table->string('website_id')->nullable();
            $table->text('categories')->nullable();
            $table->string('keywords')->nullable();
            $table->string('matchwords')->nullable();
            $table->string('kind')->nullable();
            $table->string('subtitles')->nullable();
            $table->boolean('is_generated')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->boolean('scheduled_status')->nullable();
            $table->boolean('is_processing')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_and_generate');
    }
};
