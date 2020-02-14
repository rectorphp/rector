<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheExceptionTheSecond;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheExceptionTheThird;

class ExceptionsFactoryStaticMethodWithReturnDockblock
{
    /**
     * @param int $code
     *
     * @return TheException|TheExceptionTheSecond|TheExceptionTheThird|\RuntimeException
     */
    public static function createExceptionEccolo(int $code)
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

function throwWithFactoryStaticMethodWithReturnDockblock()
{
    throw ExceptionsFactoryStaticMethodWithReturnDockblock::createExceptionEccolo(1);
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Fixture;

use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheException;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheExceptionTheSecond;
use Rector\CodingStyle\Tests\Rector\Throw_\AnnotateThrowables\Source\TheExceptionTheThird;

class ExceptionsFactoryStaticMethodWithReturnDockblock
{
    /**
     * @param int $code
     *
     * @return TheException|TheExceptionTheSecond|TheExceptionTheThird|\RuntimeException
     */
    public static function createExceptionEccolo(int $code)
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

/**
 * @throws TheException
 * @throws TheExceptionTheSecond
 * @throws TheExceptionTheThird
 * @throws \RuntimeException
 */
function throwWithFactoryStaticMethodWithReturnDockblock()
{
    throw ExceptionsFactoryStaticMethodWithReturnDockblock::createExceptionEccolo(1);
}

?>
