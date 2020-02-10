<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class CustomExceptionAlreadyAnnotated
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException
     */
    public function throwException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class CustomExceptionAlreadyAnnotated
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException
     */
    public function throwException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException();
    }
}

?>
