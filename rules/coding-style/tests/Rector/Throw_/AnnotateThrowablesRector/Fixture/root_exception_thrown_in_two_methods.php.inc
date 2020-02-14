<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

class RootExceptionThrownInTwoMethods
{
    /**
     * @throws \RuntimeException
     */
    public function throwException()
    {
        throw new \RuntimeException();
    }

    public function anotherThrowException()
    {
        throw new \RuntimeException();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

class RootExceptionThrownInTwoMethods
{
    /**
     * @throws \RuntimeException
     */
    public function throwException()
    {
        throw new \RuntimeException();
    }

    /**
     * @throws \RuntimeException
     */
    public function anotherThrowException()
    {
        throw new \RuntimeException();
    }
}

?>
