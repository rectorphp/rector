<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException;

/**
 * @throws TheException
 */
function throwCustomImportedExceptionAlreadyAnnotatedInFunction()
{
    throw new TheException();
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException;

/**
 * @throws TheException
 */
function throwCustomImportedExceptionAlreadyAnnotatedInFunction()
{
    throw new TheException();
}

?>
