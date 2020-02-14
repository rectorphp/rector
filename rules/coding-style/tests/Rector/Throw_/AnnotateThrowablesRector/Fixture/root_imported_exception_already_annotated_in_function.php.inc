<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use \RuntimeException;

/**
 * @throws RuntimeException
 */
function throwRootImportedExceptionAlreadyAnnotatedInFunction()
{
    throw new RuntimeException();
}
