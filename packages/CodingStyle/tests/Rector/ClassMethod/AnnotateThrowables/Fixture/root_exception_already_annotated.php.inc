<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class RootExceptionAlreadyAnnotated
{
    /**
     * @throws \RuntimeException
     */
    public function throwRootException()
    {
        throw new \RuntimeException('This is a root exeception');
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class RootExceptionAlreadyAnnotated
{
    /**
     * @throws \RuntimeException
     */
    public function throwRootException()
    {
        throw new \RuntimeException('This is a root exeception');
    }
}

?>
