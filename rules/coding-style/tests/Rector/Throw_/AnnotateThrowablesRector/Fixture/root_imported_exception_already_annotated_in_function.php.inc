<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use \RuntimeException;

/**
 * @throws RuntimeException
 */
function throwRootImportedExceptionAlreadyAnnotatedInFunction()
{
    throw new RuntimeException();
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use \RuntimeException;

/**
 * @throws RuntimeException
 */
function throwRootImportedExceptionAlreadyAnnotatedInFunction()
{
    throw new RuntimeException();
}

?>
