<?php

declare (strict_types=1);
namespace RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Skipper;

use Iterator;
use RectorPrefix20210511\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use RectorPrefix20210511\Symplify\Skipper\HttpKernel\SkipperKernel;
use RectorPrefix20210511\Symplify\Skipper\Skipper\Skipper;
use RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Skipper\Fixture\Element\FifthElement;
use RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Skipper\Fixture\Element\SixthSense;
use RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Skipper\Fixture\Element\ThreeMan;
use Symplify\SmartFileSystem\SmartFileInfo;
final class SkipperTest extends \RectorPrefix20210511\Symplify\PackageBuilder\Testing\AbstractKernelTestCase
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
     * @dataProvider provideDataShouldSkipFileInfo()
     */
    public function testSkipFileInfo(string $filePath, bool $expectedSkip) : void
    {
        $smartFileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($filePath);
        $resultSkip = $this->skipper->shouldSkipFileInfo($smartFileInfo);
        $this->assertSame($expectedSkip, $resultSkip);
    }
    public function provideDataShouldSkipFileInfo() : \Iterator
    {
        (yield [__DIR__ . '/Fixture/SomeRandom/file.txt', \false]);
        (yield [__DIR__ . '/Fixture/SomeSkipped/any.txt', \true]);
    }
    /**
     * @param object|class-string $element
     * @dataProvider provideDataShouldSkipElement()
     */
    public function testSkipElement($element, bool $expectedSkip) : void
    {
        $resultSkip = $this->skipper->shouldSkipElement($element);
        $this->assertSame($expectedSkip, $resultSkip);
    }
    public function provideDataShouldSkipElement() : \Iterator
    {
        (yield [\RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Skipper\Fixture\Element\ThreeMan::class, \false]);
        (yield [\RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Skipper\Fixture\Element\SixthSense::class, \true]);
        (yield [new \RectorPrefix20210511\Symplify\Skipper\Tests\Skipper\Skipper\Fixture\Element\FifthElement(), \true]);
    }
}
