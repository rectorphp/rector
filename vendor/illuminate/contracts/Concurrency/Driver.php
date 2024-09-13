<?php

namespace RectorPrefix202409\Illuminate\Contracts\Concurrency;

use Closure;
interface Driver
{
    /**
     * Run the given tasks concurrently and return an array containing the results.
     * @param \Closure|mixed[] $tasks
     */
    public function run($tasks) : array;
    /**
     * Start the given tasks in the background.
     * @param \Closure|mixed[] $tasks
     */
    public function background($tasks) : void;
}
