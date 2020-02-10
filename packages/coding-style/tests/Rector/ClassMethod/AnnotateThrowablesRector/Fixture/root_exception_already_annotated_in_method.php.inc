<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class RootExceptionAlreadyAnnotatedInMethod
{
    /**
     * @throws \RuntimeException
     */
    public function throwException()
    {
        throw new \RuntimeException();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class RootExceptionAlreadyAnnotatedInMethod
{
    /**
     * @throws \RuntimeException
     */
    public function throwException()
    {
        throw new \RuntimeException();
    }
}

?>
