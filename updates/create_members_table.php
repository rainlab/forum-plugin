<?php namespace RainLab\Forum\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateMembersTable extends Migration
{

    public function up()
    {
        Schema::create('rainlab_forum_members', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('username');
            $table->string('slug');
            $table->integer('count_posts')->index()->default(0);
            $table->integer('count_topics')->index()->default(0);
            $table->dateTime('last_active_at')->index()->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('rainlab_forum_members');
    }

}
