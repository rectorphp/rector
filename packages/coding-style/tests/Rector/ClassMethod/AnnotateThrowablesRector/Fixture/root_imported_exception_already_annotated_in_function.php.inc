<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

use \RuntimeException;

/**
 * @throws RuntimeException
 */
function throwRootImportedExceptionAlreadyAnnotatedInFunction()
{
    throw new RuntimeException();
}

?>
