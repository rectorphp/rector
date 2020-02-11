<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

/**
 * This is a comment.
 *
 * @param int $code
 */
function throwRootExceptionInFunctionWithDocblock(int $code)
{
    throw new \RuntimeException('', $code);
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

/**
 * This is a comment.
 *
 * @param int $code
 * @throws \RuntimeException
 */
function throwRootExceptionInFunctionWithDocblock(int $code)
{
    throw new \RuntimeException('', $code);
}

?>
