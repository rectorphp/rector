<?php

namespace Rector\Core\Tests\NonPhpFile\NetteDINeonMethodCallRenamer;

use Iterator;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\NonPhpFile\NetteDINeonMethodCallRenamer;
use Rector\Core\Tests\NonPhpFile\NetteDINeonMethodCallRenamer\Source\FirstService;
use Rector\Core\Tests\NonPhpFile\NetteDINeonMethodCallRenamer\Source\SecondService;
use Rector\Core\Tests\NonPhpFile\NetteDINeonMethodCallRenamer\Source\ServiceInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Renaming\Collector\MethodCallRenameCollector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

class NetteDINeonMethodCallRenamerTest extends AbstractKernelTestCase
{
    /**
     * @var NetteDINeonMethodCallRenamer
     */
    private $netteDINeonMethodCallRenamer;

    /**
     * @var MethodCallRenameCollector
     */
    private $methodCallRenameCollector;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->netteDINeonMethodCallRenamer = $this->getService(NetteDINeonMethodCallRenamer::class);
        $this->methodCallRenameCollector = $this->getService(MethodCallRenameCollector::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function testNoRenames(SmartFileInfo $fixtureFileInfo): void
    {
        $file = new File($fixtureFileInfo, $fixtureFileInfo->getContents());
        $oldContent = $file->getFileContent();
        $this->netteDINeonMethodCallRenamer->process([$file]);
        $this->assertFalse($file->hasChanged());
        $this->assertEquals($oldContent, $file->getFileContent());
    }

    /**
     * @dataProvider provideData()
     */
    public function testRenameAddToAddConfigForFirstService(SmartFileInfo $fixtureFileInfo): void
    {
        $this->markTestSkipped('Skipped - we need regex for checking only one service. If another is registered below, we need to stop processing');

        $this->methodCallRenameCollector->addMethodCallRename(
            new MethodCallRename(FirstService::class, 'add', 'addConfig')
        );

        $file = new File($fixtureFileInfo, $fixtureFileInfo->getContents());
        $this->netteDINeonMethodCallRenamer->process([$file]);

        $expected = "services:
    firstService:
        factory: Rector\Core\Tests\NonPhpFile\NetteDINeonMethodCallRenamer\Source\FirstService
        setup:
            - addConfig('key1', 'value1')
            - addConfig('key2', 'value2')

    -
        class: Rector\Core\Tests\NonPhpFile\NetteDINeonMethodCallRenamer\Source\SecondService('service-name')
        setup:
            - add('first-key', 'first-value')
            - add('second-key', 'second-value')
";

        $this->assertTrue($file->hasChanged());
        $this->assertEquals($expected, $file->getFileContent());
    }

    /**
     * @dataProvider provideData()
     */
    public function testRenameAddToAddConfigForSecondService(SmartFileInfo $fixtureFileInfo): void
    {
        $this->methodCallRenameCollector->addMethodCallRename(
            new MethodCallRename(SecondService::class, 'add', 'addConfig')
        );

        $file = new File($fixtureFileInfo, $fixtureFileInfo->getContents());
        $this->netteDINeonMethodCallRenamer->process([$file]);

        $expected = "services:
    firstService:
        factory: Rector\Core\Tests\NonPhpFile\NetteDINeonMethodCallRenamer\Source\FirstService
        setup:
            - add('key1', 'value1')
            - add('key2', 'value2')

    -
        class: Rector\Core\Tests\NonPhpFile\NetteDINeonMethodCallRenamer\Source\SecondService('service-name')
        setup:
            - addConfig('first-key', 'first-value')
            - addConfig('second-key', 'second-value')
";

        $this->assertTrue($file->hasChanged());
        $this->assertEquals($expected, $file->getFileContent());
    }

    /**
     * @dataProvider provideData()
     */
    public function testRenameAddToAddConfigForServiceInterface(SmartFileInfo $fixtureFileInfo): void
    {
        $this->markTestSkipped('Skipped - we need to load all classes via type');

        $this->methodCallRenameCollector->addMethodCallRename(
            new MethodCallRename(ServiceInterface::class, 'add', 'addConfig')
        );

        $file = new File($fixtureFileInfo, $fixtureFileInfo->getContents());
        $this->netteDINeonMethodCallRenamer->process([$file]);

        $expected = "services:
    firstService:
        factory: Rector\Core\Tests\NonPhpFile\NetteDINeonMethodCallRenamer\Source\FirstService
        setup:
            - addConfig('key1', 'value1')
            - addConfig('key2', 'value2')

    -
        class: Rector\Core\Tests\NonPhpFile\NetteDINeonMethodCallRenamer\Source\SecondService('service-name')
        setup:
            - addConfig('first-key', 'first-value')
            - addConfig('second-key', 'second-value')
";

        $this->assertTrue($file->hasChanged());
        $this->assertEquals($expected, $file->getFileContent());
    }

    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*.neon');
    }
}
