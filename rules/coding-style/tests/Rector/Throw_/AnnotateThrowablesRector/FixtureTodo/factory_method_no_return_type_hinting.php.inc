<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheExceptionTheSecond;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheExceptionTheThird;

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

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheExceptionTheSecond;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheExceptionTheThird;

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
