<?php

namespace ITBrains\QueueJobLogger\Facades;

use Illuminate\Support\Facades\Facade;

class QueueJobLogger extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \ITBrains\QueueJobLogger\QueueJobLogger::class;
    }
}
