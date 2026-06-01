<?php

namespace RectorPrefix202606\Illuminate\Contracts\Broadcasting;

interface Factory
{
    /**
     * Get a broadcaster implementation by name.
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Broadcasting\Broadcaster
     */
    public function connection($name = null);
}
