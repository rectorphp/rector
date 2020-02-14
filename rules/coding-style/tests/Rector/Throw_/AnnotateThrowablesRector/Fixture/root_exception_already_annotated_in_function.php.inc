<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

/**
 * @throws \RuntimeException
 */
function throwRootExceptionAlreadyAnnotatedInFunction()
{
    throw new \RuntimeException();
}

?>
