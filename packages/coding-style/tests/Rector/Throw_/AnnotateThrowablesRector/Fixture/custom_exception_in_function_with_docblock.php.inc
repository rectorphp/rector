<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

/**
 * This is a comment.
 *
 * @param int $code
 */
function throwCustomExceptionInFunctionWithDockblock(int $code)
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException('', $code);
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

/**
 * This is a comment.
 *
 * @param int $code
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException
 */
function throwCustomExceptionInFunctionWithDockblock(int $code)
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException('', $code);
}

?>
