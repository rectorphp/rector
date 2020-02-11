<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

/**
 * This is a comment.
 *
 * @param int $code
 */
function throwCustomExceptionInFunctionWithDockblock(int $code)
{
    throw new \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException('', $code);
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

/**
 * This is a comment.
 *
 * @param int $code
 * @throws \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException
 */
function throwCustomExceptionInFunctionWithDockblock(int $code)
{
    throw new \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException('', $code);
}

?>
