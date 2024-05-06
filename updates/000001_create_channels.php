<?php namespace RainLab\Forum\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rainlab_forum_channels', function($table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->index()->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->index()->unique();
            $table->string('description')->nullable();
            $table->integer('nest_left')->nullable();
            $table->integer('nest_right')->nullable();
            $table->integer('nest_depth')->nullable();
            $table->integer('count_topics')->default(0);
            $table->integer('count_posts')->default(0);
            $table->boolean('is_hidden')->default(0);
            $table->boolean('is_moderated')->default(0);
            $table->boolean('is_guarded')->default(0);
            $table->string('embed_code')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rainlab_forum_channels');
    }
};
