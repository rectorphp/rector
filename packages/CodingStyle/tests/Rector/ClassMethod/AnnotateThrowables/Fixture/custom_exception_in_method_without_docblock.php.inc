<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class CustomExceptionInMethodWithoutDocBlock
{
    public function throwRootException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException('This is a root exeception');
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class CustomExceptionInMethodWithoutDocBlock
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException
     */
    public function throwRootException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException('This is a root exeception');
    }
}

?>
