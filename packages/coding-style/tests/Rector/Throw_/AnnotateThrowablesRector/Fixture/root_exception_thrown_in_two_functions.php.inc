<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

/**
 * @throws \RuntimeException
 */
function throwRootExceptionThrownInTwoFunctionsFirst()
{
    throw new \RuntimeException();
}

function throwRootExceptionThrownInTwoFunctionsSecond()
{
    throw new \RuntimeException();
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

/**
 * @throws \RuntimeException
 */
function throwRootExceptionThrownInTwoFunctionsFirst()
{
    throw new \RuntimeException();
}

/**
 * @throws \RuntimeException
 */
function throwRootExceptionThrownInTwoFunctionsSecond()
{
    throw new \RuntimeException();
}

?>
