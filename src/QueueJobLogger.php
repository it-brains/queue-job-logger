<?php

namespace ITBrains\QueueJobLogger;

use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Contracts\Queue\Job;
use Log;

class QueueJobLogger
{
    const
        TYPE_PROCESSING = 'processing',
        TYPE_PROCESSED = 'processed',
        TYPE_FAILED = 'failed';

    public function set($event, string $type): void
    {
        try {
            DB::table('queue_job_logs')->insert([
                'job_id' => $event->job->getJobId(),
                'type' => $type,
                'name' => $event->job->resolveName(),
                'attempts' => $event->job->attempts(),
                'queue' => $event->job->getQueue(),
                'connection' => $event->job->getConnectionName(),
                'payload' => json_encode($event->job->payload()),
                'exception' => $this->getException($event),
                'execution_time' => $this->getExecutionTime($event->job, $type),
            ]);
        } catch(Exception $exception) {
            Log::error($exception->getMessage(), compact('exception'));
        }
    }

    protected function getExecutionTime(Job $job, string $type)
    {
        if ($type === self::TYPE_PROCESSING) return null;

        $jobBefore = DB::table('queue_job_logs')
            ->where('type', self::TYPE_PROCESSING)
            ->where('name', $job->resolveName())
            ->where('queue', $job->getQueue())
            ->where('connection', $job->getConnectionName())
            ->orderByDesc('id')
            ->first();

        if (!$jobBefore) return null;

        $duration = Carbon::now()->diffInSeconds(Carbon::parse($jobBefore->created_at));

        return gmdate('H:i:s', $duration);
    }

    /**
     * @param $event
     * @return null|string
     */
    protected function getException($event)
    {
        if (! property_exists($event, 'exception')) return null;

        return json_encode([
            'message' => $event->exception->getMessage(),
            'trace' => $event->exception->getTraceAsString(),
        ]);
    }
}
