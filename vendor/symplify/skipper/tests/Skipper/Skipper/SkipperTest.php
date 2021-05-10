<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\Skipper\Tests\Skipper\Skipper;

use Iterator;
use RectorPrefix20210510\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use RectorPrefix20210510\Symplify\Skipper\HttpKernel\SkipperKernel;
use RectorPrefix20210510\Symplify\Skipper\Skipper\Skipper;
use RectorPrefix20210510\Symplify\Skipper\Tests\Skipper\Skipper\Fixture\Element\FifthElement;
use RectorPrefix20210510\Symplify\Skipper\Tests\Skipper\Skipper\Fixture\Element\SixthSense;
use RectorPrefix20210510\Symplify\Skipper\Tests\Skipper\Skipper\Fixture\Element\ThreeMan;
use Symplify\SmartFileSystem\SmartFileInfo;
final class SkipperTest extends AbstractKernelTestCase
{
    /**
     * @var Skipper
     */
    private $skipper;
    protected function setUp() : void
    {
        $this->bootKernelWithConfigs(SkipperKernel::class, [__DIR__ . '/config/config.php']);
        $this->skipper = $this->getService(Skipper::class);
    }
    /**
     * @dataProvider provideDataShouldSkipFileInfo()
     */
    public function testSkipFileInfo(string $filePath, bool $expectedSkip) : void
    {
        $smartFileInfo = new SmartFileInfo($filePath);
        $resultSkip = $this->skipper->shouldSkipFileInfo($smartFileInfo);
        $this->assertSame($expectedSkip, $resultSkip);
    }
    public function provideDataShouldSkipFileInfo() : Iterator
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
    public function provideDataShouldSkipElement() : Iterator
    {
        (yield [ThreeMan::class, \false]);
        (yield [SixthSense::class, \true]);
        (yield [new FifthElement(), \true]);
    }
}
