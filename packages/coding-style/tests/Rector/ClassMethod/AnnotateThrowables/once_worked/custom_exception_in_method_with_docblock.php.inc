<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class CustomExceptionInMethodWithDocblock
{
    /**
     * This is a comment.
     *
     * @param int $code
     */
    public function throwException(int $code)
    {
        throw new \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException('', $code);
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class CustomExceptionInMethodWithDocblock
{
    /**
     * This is a comment.
     * @param int $code
     * @throws \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException
     */
    public function throwException(int $code)
    {
        throw new \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException('', $code);
    }
}

?>
