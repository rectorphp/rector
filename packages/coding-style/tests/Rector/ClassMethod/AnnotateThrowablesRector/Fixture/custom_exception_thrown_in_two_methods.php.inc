<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class CustomExceptionThrownInTwoMethods
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException
     */
    public function throwException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException();
    }

    public function anotherThrowException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Fixture;

class CustomExceptionThrownInTwoMethods
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException
     */
    public function throwException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException();
    }

    /**
     * @throws \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException
     */
    public function anotherThrowException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\ClassMethod\AnnotateThrowables\Source\TheException();
    }
}

?>
