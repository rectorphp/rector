<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\EasyTesting\Tests\DataProvider\StaticFixtureFinder;

use RectorPrefix20210510\PHPUnit\Framework\TestCase;
use RectorPrefix20210510\Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use RectorPrefix20210510\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;
final class StaticFixtureFinderTest extends \RectorPrefix20210510\PHPUnit\Framework\TestCase
{
    public function testYieldDirectory() : void
    {
        $files = \RectorPrefix20210510\Symplify\EasyTesting\DataProvider\StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*.php');
        $files = \iterator_to_array($files);
        $this->assertCount(2, $files);
    }
    public function testYieldDirectoryThrowException() : void
    {
        $files = \RectorPrefix20210510\Symplify\EasyTesting\DataProvider\StaticFixtureFinder::yieldDirectory(__DIR__ . '/FixtureMulti', '*.php');
        $files = \iterator_to_array($files);
        $this->assertCount(1, $files);
    }
    public function testYieldDirectoryWithRelativePathname() : void
    {
        $files = \RectorPrefix20210510\Symplify\EasyTesting\DataProvider\StaticFixtureFinder::yieldDirectoryWithRelativePathname(__DIR__ . '/Fixture', '*.php');
        $files = \iterator_to_array($files);
        $this->assertCount(2, $files);
        $this->assertArrayHasKey('foo.php', $files);
        $this->assertArrayHasKey('bar.php', $files);
    }
    public function testYieldDirectoryWithRelativePathnameThrowException() : void
    {
        $files = \RectorPrefix20210510\Symplify\EasyTesting\DataProvider\StaticFixtureFinder::yieldDirectoryWithRelativePathname(__DIR__ . '/FixtureMulti', '*.php');
        $files = \iterator_to_array($files);
        $this->assertCount(1, $files);
    }
    public function testYieldDirectoryExclusivelyThrowException() : void
    {
        $this->expectException(\RectorPrefix20210510\Symplify\SymplifyKernel\Exception\ShouldNotHappenException::class);
        $files = \RectorPrefix20210510\Symplify\EasyTesting\DataProvider\StaticFixtureFinder::yieldDirectoryExclusively(__DIR__ . '/FixtureMulti', '*.php');
        // this is needed to call the iterator
        $fileInfos = \iterator_to_array($files);
    }
}
