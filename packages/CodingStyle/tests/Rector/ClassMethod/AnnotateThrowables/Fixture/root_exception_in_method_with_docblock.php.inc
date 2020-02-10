<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class RootExceptionInMethodWithDocblock
{
    /**
     * This is a comment.
     *
     * @param int $code
     */
    public function throwRootException(int $code)
    {
        throw new \RuntimeException('This is a root exeception', $code);
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class RootExceptionInMethodWithDocblock
{
    /**
     * This is a comment.
     * @param int $code
     * @throws \RuntimeException
     */
    public function throwRootException(int $code)
    {
        throw new \RuntimeException('This is a root exeception', $code);
    }
}

?>
