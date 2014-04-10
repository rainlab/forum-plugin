<?php namespace RainLab\Forum\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePostsTable extends Migration
{

    public function up()
    {
        Schema::create('rainlab_forum_posts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title', 63);
            $table->text('content');
            $table->integer('topic_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
            $table->integer('time');
            $table->integer('edit_time');
            $table->integer('edit_user_id');
            $table->integer('delete_user_id');
            $table->integer('delete_time');
            $table->index(['topic_id', 'time'], 'topic_time');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('rainlab_forum_posts');
    }

}
