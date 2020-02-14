<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheExceptionTheSecond;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheExceptionTheThird;

class ExceptionsFactoryNoReturnTypeHingting
{
    public static function createException(int $code)
    {
        return new TheException();
    }
}

function throwWithFactoryMethodNoReturnTypeHinting()
{
    throw ExceptionsFactoryNotAnnotated::createException(1);
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheException;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheExceptionTheSecond;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowablesRector\Source\TheExceptionTheThird;

class ExceptionsFactoryNoReturnTypeHingting
{
    public static function createException(int $code)
    {
        return new TheException();
    }
}

function throwWithFactoryMethodNoReturnTypeHinting()
{
    throw ExceptionsFactoryNotAnnotated::createException(1);
}

?>
