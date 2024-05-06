<?php namespace RainLab\Forum\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rainlab_forum_posts', function($table) {
            $table->increments('id');
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->text('content_html')->nullable();
            $table->integer('count_links')->default(0);
            $table->integer('topic_id')->unsigned()->index()->nullable();
            $table->integer('member_id')->unsigned()->index()->nullable();
            $table->integer('edit_user_id')->nullable();
            $table->integer('delete_user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rainlab_forum_posts');
    }
};
