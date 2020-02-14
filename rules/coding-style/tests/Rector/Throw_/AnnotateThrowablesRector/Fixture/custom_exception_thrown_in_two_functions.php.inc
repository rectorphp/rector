<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

/**
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException
 */
function throwCustomExceptionThrownInTwoFunctionsFirst()
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException();
}

function throwCustomExceptionThrownInTwoFunctionsSecond()
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException();
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

/**
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException
 */
function throwCustomExceptionThrownInTwoFunctionsFirst()
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException();
}

/**
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException
 */
function throwCustomExceptionThrownInTwoFunctionsSecond()
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException();
}

?>
