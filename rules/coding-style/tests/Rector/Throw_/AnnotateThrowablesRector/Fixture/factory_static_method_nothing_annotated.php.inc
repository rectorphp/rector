<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\ExceptionsFactoryStaticMethodNothingAnnotated;

function throwWithFactoryStaticMethodNotAnnotated()
{
    throw ExceptionsFactoryStaticMethodNothingAnnotated::createException(1);
}

?>
