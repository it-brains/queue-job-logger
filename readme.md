# QueueJobLogger

A package for logging jobs activities.

## Install

Step 1: Require this package with composer using the following command:

``` bash
composer require it-brains/queue-job-logger
```

Step 2: Run migrations

```
php artisan migrate
```

## Available commands
```php artisan queue-job-logs:clean``` - Clean queue job logs.

## ENV variables
```
QUEUE_JOB_LOGGER_ACTIVE=true/false
```

## Testing

``` bash
composer test
```
