<?php

namespace ITBrains\QueueJobLogger\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Schema;

class QueueJobLoggerTest extends \ITBrains\QueueJobLogger\Tests\TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_run_migration()
    {
        $this->assertTrue(Schema::hasTable('queue_job_logs'));

        $this->assertTrue(Schema::hasColumns('queue_job_logs', [
            'id',
            'job_id',
            'type',
            'name',
            'attempts',
            'queue',
            'connection',
            'payload',
            'exception',
            'execution_time',
            'created_at',
        ]));
    }
}
