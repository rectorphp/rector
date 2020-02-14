<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

function throwRootExceptionInFunctionWithoutDocblock()
{
    throw new \RuntimeException();
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

/**
 * @throws \RuntimeException
 */
function throwRootExceptionInFunctionWithoutDocblock()
{
    throw new \RuntimeException();
}

?>
