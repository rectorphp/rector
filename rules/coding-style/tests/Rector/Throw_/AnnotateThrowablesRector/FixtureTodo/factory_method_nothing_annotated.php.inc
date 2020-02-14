<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheExceptionTheSecond;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheExceptionTheThird;

class ExceptionsFactoryMethodNothingAnnotated
{
    public function cercoQuestoMetodoQui(int $code)
    {
        switch ($code) {
            case 1:
                return new TheException();
            case 2:
                return new TheExceptionTheSecond();
            case 3:
                return new TheExceptionTheThird();
            default:
                return new \RuntimeException();
        }
    }
}

function throwWithFactoryMethodNotAnnotated()
{
    $factory = new ExceptionsFactoryMethodNothingAnnotated();
    throw $factory->cercoQuestoMetodoQui(1);
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheExceptionTheSecond;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheExceptionTheThird;

class ExceptionsFactoryMethodNothingAnnotated
{
    public function cercoQuestoMetodoQui(int $code)
    {
        switch ($code) {
            case 1:
                return new TheException();
            case 2:
                return new TheExceptionTheSecond();
            case 3:
                return new TheExceptionTheThird();
            default:
                return new \RuntimeException();
        }
    }
}

function throwWithFactoryMethodNotAnnotated()
{
    $factory = new ExceptionsFactoryMethodNothingAnnotated();
    throw $factory->cercoQuestoMetodoQui(1);
}

?>
