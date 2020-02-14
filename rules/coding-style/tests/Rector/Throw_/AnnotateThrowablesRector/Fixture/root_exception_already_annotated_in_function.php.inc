<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

/**
 * @throws \RuntimeException
 */
function throwRootExceptionAlreadyAnnotatedInFunction()
{
    throw new \RuntimeException();
}
