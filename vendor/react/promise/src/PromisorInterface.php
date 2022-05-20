<?php

namespace RectorPrefix20220520\React\Promise;

interface PromisorInterface
{
    /**
     * Returns the promise of the deferred.
     *
     * @return PromiseInterface
     */
    public function promise();
}
