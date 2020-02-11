<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

/**
 * @throws \RuntimeException
 */
function throwRootExceptionAlreadyAnnotatedInFunction()
{
    throw new \RuntimeException();
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

/**
 * @throws \RuntimeException
 */
function throwRootExceptionAlreadyAnnotatedInFunction()
{
    throw new \RuntimeException();
}

?>
