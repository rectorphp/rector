<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;

function throwWithFactoryMethodNoReturnTypeHinting()
{
    $exceptionFactory = new ExceptionsFactoryMethodNothingAnnotated();
    throw $exceptionFactory->createException(1);
}
