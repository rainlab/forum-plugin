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
            $table->string('title', 63);
            $table->integer('channel_id')->unsigned()->index();
            $table->boolean('private')->index();
            $table->boolean('sticky');
            $table->boolean('locked')->index();
            $table->integer('count_posts')->index();
            $table->integer('start_member_id')->index();
            $table->integer('last_post_member_id');
            $table->dateTime('start_time')->index();
            $table->dateTime('last_post_time')->index();
            $table->index(['sticky', 'last_post_time'], 'sticky_post_time');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('rainlab_forum_topics');
    }

}
