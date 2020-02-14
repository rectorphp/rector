<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

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
