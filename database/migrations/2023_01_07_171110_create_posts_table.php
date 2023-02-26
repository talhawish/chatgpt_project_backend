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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('author')->nullable();
            $table->string('wp_post_id')->nullable();
            $table->string('import_and_generate_id')->nullable();
            $table->string('slug')->nullable();
            $table->date('date')->nullable();
            $table->date('date_gmt')->nullable();
            $table->string('guid')->nullable();
            $table->string('type')->nullable();
            $table->string('link')->nullable();
            $table->string('status')->default('publish'); // One of: publish, future, draft, pending, private
            $table->string('website_id');
            $table->string('title');
            $table->longText('content');
            $table->longText('excerpt')->nullable();;
            $table->string('comment_status')->default('closed'); // One of: open, closed
            $table->string('ping_status')->default('closed'); // One of: open, closed
            $table->text('meta')->nullable();
            $table->text('categories');
            $table->text('tags')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('updated')->nullable()->comment('updated locally and not published to remote server');
            $table->date('scheduled_at')->nullable();
            $table->boolean('published_status')->nullable()->default(false);
            $table->boolean('needs_update')->nullable()->default(false);
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
        Schema::dropIfExists('posts');
    }
};
