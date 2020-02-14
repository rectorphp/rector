<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

class CustomExceptionThrownInTwoMethods
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException
     */
    public function throwException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException();
    }

    public function anotherThrowException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

class CustomExceptionThrownInTwoMethods
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException
     */
    public function throwException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException();
    }

    /**
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException
     */
    public function anotherThrowException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException();
    }
}

?>
