<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\Skipper\Tests\Skipper\Skip;

use Iterator;
use RectorPrefix20210510\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use RectorPrefix20210510\Symplify\Skipper\HttpKernel\SkipperKernel;
use RectorPrefix20210510\Symplify\Skipper\Skipper\Skipper;
use RectorPrefix20210510\Symplify\Skipper\Tests\Skipper\Skip\Source\AnotherClassToSkip;
use RectorPrefix20210510\Symplify\Skipper\Tests\Skipper\Skip\Source\NotSkippedClass;
use RectorPrefix20210510\Symplify\Skipper\Tests\Skipper\Skip\Source\SomeClassToSkip;
use Symplify\SmartFileSystem\SmartFileInfo;
final class SkipSkipperTest extends AbstractKernelTestCase
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
     * @dataProvider provideCheckerAndFile()
     * @dataProvider provideCodeAndFile()
     * @dataProvider provideMessageAndFile()
     * @dataProvider provideAnythingAndFilePath()
     */
    public function test(string $element, string $filePath, bool $expectedSkip) : void
    {
        $resolvedSkip = $this->skipper->shouldSkipElementAndFileInfo($element, new SmartFileInfo($filePath));
        $this->assertSame($expectedSkip, $resolvedSkip);
    }
    public function provideCheckerAndFile() : Iterator
    {
        (yield [SomeClassToSkip::class, __DIR__ . '/Fixture', \true]);
        (yield [AnotherClassToSkip::class, __DIR__ . '/Fixture/someFile', \true]);
        (yield [AnotherClassToSkip::class, __DIR__ . '/Fixture/someDirectory/anotherFile.php', \true]);
        (yield [AnotherClassToSkip::class, __DIR__ . '/Fixture/someDirectory/anotherFile.php', \true]);
        (yield [NotSkippedClass::class, __DIR__ . '/Fixture/someFile', \false]);
        (yield [NotSkippedClass::class, __DIR__ . '/Fixture/someOtherFile', \false]);
    }
    public function provideCodeAndFile() : Iterator
    {
        (yield [AnotherClassToSkip::class . '.someCode', __DIR__ . '/Fixture/someFile', \true]);
        (yield [AnotherClassToSkip::class . '.someOtherCode', __DIR__ . '/Fixture/someDirectory/someFile', \true]);
        (yield [AnotherClassToSkip::class . '.someAnotherCode', __DIR__ . '/Fixture/someDirectory/someFile', \true]);
        (yield ['someSniff.someForeignCode', __DIR__ . '/Fixture/someFile', \false]);
        (yield ['someSniff.someOtherCode', __DIR__ . '/Fixture/someFile', \false]);
    }
    public function provideMessageAndFile() : Iterator
    {
        (yield ['some fishy code at line 5!', __DIR__ . '/Fixture/someFile', \true]);
        (yield ['some another fishy code at line 5!', __DIR__ . '/Fixture/someDirectory/someFile.php', \true]);
        (yield ['Cognitive complexity for method "foo" is 2 but has to be less than or equal to 1.', __DIR__ . '/Fixture/skip.php.inc', \true]);
        (yield ['Cognitive complexity for method "bar" is 2 but has to be less than or equal to 1.', __DIR__ . '/Fixture/skip.php.inc', \false]);
    }
    public function provideAnythingAndFilePath() : Iterator
    {
        (yield ['anything', __DIR__ . '/Fixture/AlwaysSkippedPath/some_file.txt', \true]);
        (yield ['anything', __DIR__ . '/Fixture/PathSkippedWithMask/another_file.txt', \true]);
    }
}
