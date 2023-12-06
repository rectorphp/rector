<?php

declare(strict_types=1);

namespace PHPUnit\Framework;

use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\MockObject\MockObject;

if (! class_exists('PHPUnit\Framework\TestCase')) {
    abstract class TestCase
    {
        /**
         * @psalm-template RealInstanceType of object
         *
         * @psalm-param class-string<RealInstanceType> $originalClassName
         *
         * @psalm-return MockObject&RealInstanceType
         */
        protected function createMock(string $originalClassName): MockObject
        {
        }
    }
}
