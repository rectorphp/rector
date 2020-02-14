<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

class CustomExceptionAlreadyAnnotatedInMethod
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException
     */
    public function throwException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

class CustomExceptionAlreadyAnnotatedInMethod
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException
     */
    public function throwException()
    {
        throw new \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException();
    }
}

?>
