<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException;

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

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException;

/**
 * @throws TheException
 */
function throwCustomImportedExceptionAlreadyAnnotatedInFunction()
{
    throw new TheException();
}

?>
