<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\ExceptionsFactoryMethodWithReturnDocblock;

function throwWithFactoryMethodWithReturnDocblock()
{
    $exceptionFactory = new ExceptionsFactoryMethodWithReturnDocblock();
    throw $exceptionFactory->createException(1);
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\ExceptionsFactoryMethodWithReturnDocblock;

/**
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheSecond
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheThird
 * @throws \RuntimeException
 */
function throwWithFactoryMethodWithReturnDocblock()
{
    $exceptionFactory = new ExceptionsFactoryMethodWithReturnDocblock();
    throw $exceptionFactory->createException(1);
}

?>
