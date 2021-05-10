<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\EasyTesting\Tests\PHPUnit\Behavior\DirectoryAssertableTrait;

use RectorPrefix20210510\PHPUnit\Framework\ExpectationFailedException;
use RectorPrefix20210510\PHPUnit\Framework\TestCase;
use RectorPrefix20210510\Symplify\EasyTesting\PHPUnit\Behavior\DirectoryAssertableTrait;
use Throwable;
final class DirectoryAssertableTraitTest extends TestCase
{
    use DirectoryAssertableTrait;
    public function testSuccess() : void
    {
        $this->assertDirectoryEquals(__DIR__ . '/Fixture/first_directory', __DIR__ . '/Fixture/second_directory');
    }
    public function testFail() : void
    {
        $throwable = null;
        try {
            $this->assertDirectoryEquals(__DIR__ . '/Fixture/first_directory', __DIR__ . '/Fixture/third_directory');
        } catch (Throwable $throwable) {
        } finally {
            $this->assertInstanceOf(ExpectationFailedException::class, $throwable);
        }
    }
}
