<?php namespace RainLab\Forum\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rainlab_forum_members', function($table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index()->nullable();
            $table->string('username')->nullable();
            $table->string('slug')->nullable();
            $table->integer('count_posts')->index()->default(0);
            $table->integer('count_topics')->index()->default(0);
            $table->dateTime('last_active_at')->index()->nullable();
            $table->boolean('is_moderator')->default(0)->index();
            $table->boolean('is_banned')->default(0);
            $table->boolean('is_approved')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rainlab_forum_members');
    }
};
