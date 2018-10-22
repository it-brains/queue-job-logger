<?php

namespace ITBrains\QueueJobLogger;

use Illuminate\Contracts\Container\Container;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application as LaravelApplication;
use ITBrains\QueueJobLogger\Console\Commands\QueueJobLogsClean;

class QueueJobLoggerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->setupConfig($this->app);

        $this->setupMigrations($this->app);

        $this->setupListeners($this->app->queue, $this->app->config->get('queue-job-logger'));

        $this->setupConsole($this->app);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(QueueJobLogger::class, function () {
            return new QueueJobLogger;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [QueueJobLogger::class];
    }

    /**
     * Setup the config.
     *
     * @param Container $app
     */
    protected function setupConfig(Container $app): void
    {
        $source = realpath($raw = __DIR__.'/../config/queue-job-logger.php') ?: $raw;

        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            $this->publishes([$source => config_path('queue-job-logger.php')]);
        }

        $this->mergeConfigFrom($source, 'queue-job-logger');
    }

    /**
     * Setup the config.
     *
     * @param Container $app
     */
    protected function setupConsole(Container $app): void
    {
        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            $this->commands([
                QueueJobLogsClean::class,
            ]);
        }
    }

    /**
     * Setup the migrations.
     *
     * @param Container $app
     */
    protected function setupMigrations(Container $app): void
    {
        $source = realpath($raw = __DIR__.'/../database/migrations') ?: $raw;

        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            $this->publishes([$source => database_path('migrations')]);
        }

        $this->loadMigrationsFrom($source);
    }

    /**
     * Setup the queue.
     *
     * @param QueueManager $queue
     * @param array $config
     *
     * @return void
     */
    protected function setupListeners(QueueManager $queue, array $config): void
    {
        if (isset($config['active']) && !$config['active']) return;
        
        $this->setupListenerOnBeforeJobEvent($queue);

        $this->setupListenerOnAfterJobEvent($queue);

        $this->setupListenerOnFailJobEvent($queue);
    }

    /**
     * Setup the queue.
     *
     * @param QueueManager $queue
     *
     * @return void
     */
    protected function setupListenerOnBeforeJobEvent(QueueManager $queue): void
    {
        if (!class_exists(JobProcessing::class)) {
            return;
        }

        $queue->before(function (JobProcessing $event) {
            $this->app->make(QueueJobLogger::class)->set($event, QueueJobLogger::TYPE_PROCESSING);
        });
    }

    /**
     * Setup the queue.
     *
     * @param QueueManager $queue
     *
     * @return void
     */
    protected function setupListenerOnAfterJobEvent(QueueManager $queue): void
    {
        if (!class_exists(JobProcessed::class)) {
            return;
        }

        $queue->after(function (JobProcessed $event) {
            $this->app->make(QueueJobLogger::class)->set($event, QueueJobLogger::TYPE_PROCESSED);
        });
    }

    /**
     * Setup the queue.
     *
     * @param QueueManager $queue
     *
     * @return void
     */
    protected function setupListenerOnFailJobEvent(QueueManager $queue): void
    {
        if (!class_exists(JobExceptionOccurred::class)) {
            return;
        }

        $queue->exceptionOccurred(function (JobExceptionOccurred $event) {
            $this->app->make(QueueJobLogger::class)->set($event, QueueJobLogger::TYPE_FAILED);
        });
    }
}
