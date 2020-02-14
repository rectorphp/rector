<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException;

/**
 * @throws TheException
 */
function skipCustomImportedExceptionAlreadyAnnotatedInFunction()
{
    throw new TheException();
}
