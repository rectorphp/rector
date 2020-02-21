<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\ExceptionsFactoryMethodNothingAnnotated;

function throwWithFactoryMethodNotAnnotated()
{
    $factory = new ExceptionsFactoryMethodNothingAnnotated();
    throw $factory->isThis(1);
}
