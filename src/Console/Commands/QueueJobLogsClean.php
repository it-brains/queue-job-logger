<?php

namespace ITBrains\QueueJobLogger\Console\Commands;

use DB;
use Exception;
use Illuminate\Console\Command;
use Log;

class QueueJobLogsClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue-job-logs:clean {before : The date before that all records will be removed.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean queue job logs.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $before = $this->argument('before');

            DB::table('queue_job_logs')
                ->whereDate('created_at', '<=', $before)
                ->delete();
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), compact('exception'));

            $this->error($exception->getMessage());
        }
    }
}
