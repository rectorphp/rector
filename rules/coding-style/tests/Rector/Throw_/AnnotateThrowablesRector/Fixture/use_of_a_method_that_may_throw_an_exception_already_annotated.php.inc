<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\MethodThatMayThrowAnException;

class UseOfAMethodThatMayThrowAnExceptionAlreadyAnnotated
{
    /**
     * @return int
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException
     */
    public function iUseAMethodThatMayThrowAnException():int
    {
        $class = new MethodThatMayThrowAnException();
        return $class->mayThrowAnException(1);
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\MethodThatMayThrowAnException;

class UseOfAMethodThatMayThrowAnExceptionAlreadyAnnotated
{
    /**
     * @return int
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheException
     * @throws \Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\Exceptions\TheExceptionTheSecond
     */
    public function iUseAMethodThatMayThrowAnException():int
    {
        $class = new MethodThatMayThrowAnException();
        return $class->mayThrowAnException(1);
    }
}

?>
