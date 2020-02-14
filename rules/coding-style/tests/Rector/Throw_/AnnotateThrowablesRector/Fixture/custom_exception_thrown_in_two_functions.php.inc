<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

/**
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException
 */
function throwCustomExceptionThrownInTwoFunctionsFirst()
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException();
}

function throwCustomExceptionThrownInTwoFunctionsSecond()
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException();
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

/**
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException
 */
function throwCustomExceptionThrownInTwoFunctionsFirst()
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException();
}

/**
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException
 */
function throwCustomExceptionThrownInTwoFunctionsSecond()
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException();
}

?>
