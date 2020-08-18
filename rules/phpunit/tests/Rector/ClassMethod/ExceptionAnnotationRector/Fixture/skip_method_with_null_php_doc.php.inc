<?php

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\ExceptionAnnotationRector\Fixture;

use PHPUnit\Framework\TestCase;

class SkipMethodWithNullPhpDoc extends TestCase
{
    public function testLiteralMessage(): void
    {
        $this->expectException('Exception');
        throw new \Exception('A literal exception message');
    }

    // test

    public function testPartialMessageBegin(): void
    {
        throw new \Exception('A partial exception message');
    }
}
