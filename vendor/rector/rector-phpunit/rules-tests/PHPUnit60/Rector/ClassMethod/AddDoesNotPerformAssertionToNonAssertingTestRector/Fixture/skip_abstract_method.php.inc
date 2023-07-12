<?php

namespace Rector\Tests\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Fixture;

use PHPUnit\Framework\TestCase;

abstract class SkipAbstractMethod extends TestCase
{
    abstract public function testSomeThing(): void;
}

?>
