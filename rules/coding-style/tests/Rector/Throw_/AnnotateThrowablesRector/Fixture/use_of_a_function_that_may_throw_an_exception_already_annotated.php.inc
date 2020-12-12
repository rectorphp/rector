<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use function Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\i_throw_an_exception;
use \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;

class UseOfAFunctionThatMayThrowAnExceptionAlreadyAnnotated
{
    /**
     * @throws TheException
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheSecond
     */
    public function iUseAFunctionThatMayThrowAnException()
    {
        return i_throw_an_exception();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use function Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\i_throw_an_exception;
use \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException;

class UseOfAFunctionThatMayThrowAnExceptionAlreadyAnnotated
{
    /**
     * @throws TheException
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheSecond
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheThird
     */
    public function iUseAFunctionThatMayThrowAnException()
    {
        return i_throw_an_exception();
    }
}

?>
