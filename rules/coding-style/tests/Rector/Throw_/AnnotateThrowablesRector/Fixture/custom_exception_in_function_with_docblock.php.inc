<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

/**
 * This is a comment.
 *
 * @param int $code
 */
function throwCustomExceptionInFunctionWithDockblock(int $code)
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException('', $code);
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

/**
 * This is a comment.
 *
 * @param int $code
 * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException
 */
function throwCustomExceptionInFunctionWithDockblock(int $code)
{
    throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException('', $code);
}

?>
