<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException;

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
