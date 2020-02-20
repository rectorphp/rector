<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\ExceptionsFactoryStaticMethodWithReturnDocblock;

function throwWithFactoryStaticMethodWithReturnDocblock()
{
    throw ExceptionsFactoryStaticMethodWithReturnDocblock::createException(1);
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\ExceptionsFactoryStaticMethodWithReturnDocblock;

/**
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheSecond
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheThird
 * @throws \RuntimeException
 */
function throwWithFactoryStaticMethodWithReturnDocblock()
{
    throw ExceptionsFactoryStaticMethodWithReturnDocblock::createException(1);
}

?>
