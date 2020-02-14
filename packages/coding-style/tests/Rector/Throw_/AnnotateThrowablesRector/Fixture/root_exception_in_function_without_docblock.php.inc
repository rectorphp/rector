<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

function throwRootExceptionInFunctionWithoutDocblock()
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
function throwRootExceptionInFunctionWithoutDocblock()
{
    throw new \RuntimeException();
}

?>
