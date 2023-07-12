<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Tests\PHPUnit50\Rector\StaticCall\GetMockRector\Source;

final class SomeExceptionWithGetMock extends \Exception
{
    public function getMock()
    {
    }
}
