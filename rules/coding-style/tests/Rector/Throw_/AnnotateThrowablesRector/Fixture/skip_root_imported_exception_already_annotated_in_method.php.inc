<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use \RuntimeException;

class RootImportedExceptionAlreadyAnnotatedInMethod
{
    /**
     * @throws RuntimeException
     */
    public function throwException()
    {
        throw new RuntimeException();
    }
}
