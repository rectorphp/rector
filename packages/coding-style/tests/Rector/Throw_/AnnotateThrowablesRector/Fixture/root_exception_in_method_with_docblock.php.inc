<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

class RootExceptionInMethodWithDocblock
{
    /**
     * This is a comment.
     *
     * @param int $code
     */
    public function throwException(int $code)
    {
        throw new \RuntimeException('', $code);
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

class RootExceptionInMethodWithDocblock
{
    /**
     * This is a comment.
     *
     * @param int $code
     * @throws \RuntimeException
     */
    public function throwException(int $code)
    {
        throw new \RuntimeException('', $code);
    }
}

?>
