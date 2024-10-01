<?php

namespace RectorPrefix202410\Illuminate\Contracts\Concurrency;

use Closure;
use RectorPrefix202410\Illuminate\Support\Defer\DeferredCallback;
interface Driver
{
    /**
     * Run the given tasks concurrently and return an array containing the results.
     * @param \Closure|mixed[] $tasks
     */
    public function run($tasks) : array;
    /**
     * Defer the execution of the given tasks.
     * @param \Closure|mixed[] $tasks
     */
    public function defer($tasks) : DeferredCallback;
}
