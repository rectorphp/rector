<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

/**
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException
 */
function throwCustomExceptionAlreadyAnnotatedInFunction()
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException();
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

/**
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException
 */
function throwCustomExceptionAlreadyAnnotatedInFunction()
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException();
}

?>
