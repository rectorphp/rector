<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class RootExceptionInMethodWithoutDocblock
{
    public function throwRootException()
    {
        throw new \RuntimeException('This is a root exeception');
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class RootExceptionInMethodWithoutDocblock
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
