<?php namespace RainLab\Forum\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rainlab_forum_topic_followers', function($table) {
            $table->integer('topic_id')->unsigned();
            $table->integer('member_id')->unsigned();
            $table->primary(['topic_id', 'member_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rainlab_forum_topic_followers');
    }
};
