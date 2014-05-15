<?php namespace RainLab\Forum\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTopicsTable extends Migration
{

    public function up()
    {
        Schema::create('rainlab_forum_topics', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('subject')->nullable();
            $table->string('slug')->index()->unique();
            $table->integer('channel_id')->unsigned()->index();
            $table->integer('start_member_id')->index();
            $table->integer('last_post_id')->nullable();
            $table->integer('last_post_member_id')->nullable();
            $table->dateTime('last_post_at')->index()->nullable();
            $table->boolean('private')->index()->nullable();
            $table->boolean('sticky')->nullable();
            $table->boolean('locked')->index()->nullable();
            $table->integer('count_posts')->index()->nullable();
            $table->integer('count_views')->index()->nullable();
            $table->index(['sticky', 'last_post_at'], 'sticky_post_time');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('rainlab_forum_topics');
    }

}
