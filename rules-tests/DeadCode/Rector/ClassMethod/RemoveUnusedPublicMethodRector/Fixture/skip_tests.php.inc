<?php
namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipTests extends TestCase
{
    public function test(): void
    {
        $this->assertTrue('yes');
    }
}
