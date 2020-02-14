<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

class CustomExceptionAlreadyAnnotatedInMethod
{
    public function throwException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

class CustomExceptionAlreadyAnnotatedInMethod
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException
     */
    public function throwException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException();
    }
}

?>
