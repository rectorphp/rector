<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\ExceptionsFactoryStaticMethodWithReturnDocblock;

function throwWithFactoryStaticMethodWithReturnDockblock()
{
    throw ExceptionsFactoryStaticMethodWithReturnDocblock::createExceptionEccolo(1);
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheSecond;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheThird;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\ExceptionsFactoryStaticMethodWithReturnDocblock;

/**
 * @throws TheException
 * @throws TheExceptionTheSecond
 * @throws TheExceptionTheThird
 * @throws \RuntimeException
 */
function throwWithFactoryStaticMethodWithReturnDockblock()
{
    throw ExceptionsFactoryStaticMethodWithReturnDocblock::createExceptionEccolo(1);
}

?>
