<?php
namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipTestDataProvider extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(): void
    {
        $this->assertTrue('yes');
    }

    public function provideData()
    {
        return [1, 2, 3];
    }
}
