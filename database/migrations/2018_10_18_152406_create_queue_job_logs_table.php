<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueueJobLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queue_job_logs', function (Blueprint $table) {
            $table->increments('id');

            $table->string('job_id');
            $table->string('type');
            $table->string('name');
            $table->integer('attempts')->unsigned();
            $table->string('queue');
            $table->string('connection');
            $table->longText('payload');
            $table->longText('exception')->nullable();
            $table->time('execution_time')->nullable();

            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('queue_job_logs');
    }
}
