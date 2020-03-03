<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

class UseOfAFunctionThatThrowsAnException
{
    public function iUseAFunctionThatMayThrowAnException()
    {
        return i_throw_an_exception();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

class UseOfAFunctionThatThrowsAnException
{
    /**
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheSecond
     */
    public function iUseAFunctionThatMayThrowAnException()
    {
        return i_throw_an_exception();
    }
}

?>
