<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;

class SkipCustomImportedExceptionAlreadyAnnotatedInMethod
{
    /**
     * @throws TheException
     */
    public function throwException()
    {
        throw new TheException();
    }
}
