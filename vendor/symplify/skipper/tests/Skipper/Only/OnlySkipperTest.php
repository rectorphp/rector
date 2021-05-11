<?php

declare (strict_types=1);
namespace RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only;

use Iterator;
use RectorPrefix20210511\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use RectorPrefix20210511\Symplify\Skipper\HttpKernel\SkipperKernel;
use RectorPrefix20210511\Symplify\Skipper\Skipper\Skipper;
use RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\IncludeThisClass;
use RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\SkipCompletely;
use RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\SkipCompletelyToo;
use RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\SkipThisClass;
use Symplify\SmartFileSystem\SmartFileInfo;
final class OnlySkipperTest extends \RectorPrefix20210511\Symplify\PackageBuilder\Testing\AbstractKernelTestCase
{
    /**
     * @var Skipper
     */
    private $skipper;
    protected function setUp() : void
    {
        $this->bootKernelWithConfigs(\RectorPrefix20210511\Symplify\Skipper\HttpKernel\SkipperKernel::class, [__DIR__ . '/config/config.php']);
        $this->skipper = $this->getService(\RectorPrefix20210511\Symplify\Skipper\Skipper\Skipper::class);
    }
    /**
     * @dataProvider provideCheckerAndFile()
     */
    public function testCheckerAndFile(string $class, string $filePath, bool $expected) : void
    {
        $resolvedSkip = $this->skipper->shouldSkipElementAndFileInfo($class, new \Symplify\SmartFileSystem\SmartFileInfo($filePath));
        $this->assertSame($expected, $resolvedSkip);
    }
    public function provideCheckerAndFile() : \Iterator
    {
        (yield [\RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\IncludeThisClass::class, __DIR__ . '/Fixture/SomeFileToOnlyInclude.php', \false]);
        (yield [\RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\IncludeThisClass::class, __DIR__ . '/Fixture/SomeFile.php', \true]);
        // no restrictions
        (yield [\RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\SkipThisClass::class, __DIR__ . '/Fixture/SomeFileToOnlyInclude.php', \false]);
        (yield [\RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\SkipThisClass::class, __DIR__ . '/Fixture/SomeFile.php', \false]);
        (yield [\RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\SkipCompletely::class, __DIR__ . '/Fixture/SomeFile.php', \true]);
        (yield [\RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Only\Source\SkipCompletelyToo::class, __DIR__ . '/Fixture/SomeFile.php', \true]);
    }
}
