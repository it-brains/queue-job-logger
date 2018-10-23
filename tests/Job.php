<?php

namespace ITBrains\QueueJobLogger\Tests;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var bool
     */
    private $executeWithException;

    public function __construct($executeWithException = false)
    {
        $this->executeWithException = $executeWithException;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        if ($this->executeWithException) {
            throw new Exception();
        }
    }
}
