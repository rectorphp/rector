<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;

/**
 * @throws TheException
 */
function skipCustomImportedExceptionAlreadyAnnotatedInFunction()
{
    throw new TheException();
}
