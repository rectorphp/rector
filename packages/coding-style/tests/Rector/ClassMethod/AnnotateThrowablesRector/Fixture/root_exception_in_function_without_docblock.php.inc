<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

function throwRootExceptionInFunctionWithoutDocblock()
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
function throwRootExceptionInFunctionWithoutDocblock()
{
    throw new \RuntimeException();
}

?>
