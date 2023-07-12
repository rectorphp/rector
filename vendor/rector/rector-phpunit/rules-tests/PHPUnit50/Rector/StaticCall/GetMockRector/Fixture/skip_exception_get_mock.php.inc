<?php

namespace Rector\PHPUnit\Tests\PHPUnit50\Rector\StaticCall\GetMockRector\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\PHPUnit\Tests\PHPUnit50\Rector\StaticCall\GetMockRector\Source\SomeExceptionWithGetMock;

final class SkipExceptionGetMock extends TestCase
{
    public function test()
    {
        try {
            parent::runTest();
        } catch (SomeExceptionWithGetMock $someExceptionWithGetMock) {
            $failedMock = $someExceptionWithGetMock->getMock();
        }
    }
}
