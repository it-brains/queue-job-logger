<?php

namespace ITBrains\QueueJobLogger\Tests\Feature;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DB;
use Illuminate\Support\Carbon;
use ITBrains\QueueJobLogger\Tests\Job;
use ITBrains\QueueJobLogger\QueueJobLogger;
use ITBrains\QueueJobLogger\Tests\TestCase;
use Schema;

class QueueJobLoggerTest extends TestCase
{
    use RefreshDatabase;

    protected $table = 'queue_job_logs';

    /**
     * @test
     */
    public function it_run_migration(): void
    {
        $this->assertTrue(Schema::hasTable($this->table));

        $this->assertTrue(Schema::hasColumns($this->table, [
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

    /**
     * @test
     * @dataProvider statusesDataProvider
     * @param $isExecuteJobWithException
     * @param $beforeExecuteStatus
     * @param $afterExecuteStatus
     */
    public function it_write_logs_to_db_when_job_executed(
        $isExecuteJobWithException,
        $beforeExecuteStatus,
        $afterExecuteStatus
    ) {
        try {
            dispatch(new Job($isExecuteJobWithException));
        } catch (Exception $e) {}

        $this->assertDatabaseHas($this->table, [
            'name' => Job::class,
            'type' => $beforeExecuteStatus,
        ]);

        $this->assertDatabaseHas($this->table, [
            'name' => Job::class,
            'type' => $afterExecuteStatus,
        ]);
    }

    /**
     * @test
     * @dataProvider cleanDatesDataProvider
     * @param string $cleanDate
     * @param int $countLastLogs
     */
    public function it_clean_logs_in_db_by_date(string $cleanDate, int $countLastLogs): void
    {
        $count = $this->fillFakeLogs();

        $this->assertTrue(DB::table($this->table)->count() === $count);

        $this->artisan("queue-job-logs:clean", [
          'before' => $cleanDate
        ]);

        $this->assertTrue(DB::table($this->table)->count() === $countLastLogs);
    }

    public function statusesDataProvider(): array
    {
        return [
            [false, QueueJobLogger::TYPE_PROCESSING, QueueJobLogger::TYPE_PROCESSED],
            [true, QueueJobLogger::TYPE_PROCESSING, QueueJobLogger::TYPE_FAILED],
        ];
    }

    public function cleanDatesDataProvider(): array
    {
        return [
            [Carbon::now()->toDateString(), 0],
            [Carbon::now()->addDays(1)->toDateString(), 0],
            [Carbon::now()->subDays(1)->toDateString(), 1],
            [Carbon::now()->subDays(2)->toDateString(), 2],
        ];
    }

    protected function fillFakeLogs(): int
    {
        $count = rand(5, 10);

        for ($i = 0; $i < $count; $i++) {
            DB::table($this->table)->insert([
                'job_id' => '',
                'type' => array_rand([
                    QueueJobLogger::TYPE_PROCESSING,
                    QueueJobLogger::TYPE_PROCESSED,
                    QueueJobLogger::TYPE_FAILED
                ]),
                'name' => substr(md5(mt_rand()), 0, 7),
                'attempts' => 0,
                'queue' => substr(md5(mt_rand()), 0, 7),
                'connection' => substr(md5(mt_rand()), 0, 7),
                'payload' => '',
                'exception' => null,
                'execution_time' => null,
                'created_at' =>  Carbon::now()->subDays($i)->toDateTimeString(),
            ]);
        }

        return $count;
    }
}
