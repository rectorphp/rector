<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

class CustomExceptionInMethodWithDocblock
{
    /**
     * This is a comment.
     *
     * @param int $code
     */
    public function throwException(int $code)
    {
        throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException('', $code);
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

class CustomExceptionInMethodWithDocblock
{
    /**
     * This is a comment.
     *
     * @param int $code
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException
     */
    public function throwException(int $code)
    {
        throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException('', $code);
    }
}

?>
