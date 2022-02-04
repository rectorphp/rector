<?php

namespace RectorPrefix20220204\React\Promise;

interface PromisorInterface
{
    /**
     * Returns the promise of the deferred.
     *
     * @return PromiseInterface
     */
    public function promise();
}
