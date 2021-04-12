<?php

namespace Rector\Core\Tests\NonPhpFile\NeonMethodCallRenamer;

use Iterator;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\NonPhpFile\NeonMethodCallRenamer;
use Rector\Core\Tests\NonPhpFile\NeonMethodCallRenamer\Source\FirstService;
use Rector\Core\Tests\NonPhpFile\NeonMethodCallRenamer\Source\SecondService;
use Rector\Core\Tests\NonPhpFile\NeonMethodCallRenamer\Source\ServiceInterface;
use Rector\Renaming\Configuration\MethodCallRenameCollector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

class NeonMethodCallRenamerTest extends AbstractKernelTestCase
{
    /**
     * @var NeonMethodCallRenamer
     */
    private $neonMethodCallRenamer;

    /**
     * @var MethodCallRenameCollector
     */
    private $methodCallRenameCollector;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->neonMethodCallRenamer = $this->getService(NeonMethodCallRenamer::class);
        $this->methodCallRenameCollector = $this->getService(MethodCallRenameCollector::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function testNoRenames(SmartFileInfo $fixtureFileInfo): void
    {
        $result = $this->neonMethodCallRenamer->process($fixtureFileInfo);
        $this->assertEquals($fixtureFileInfo->getContents(), $result->getNewContent());
    }

    /**
     * @dataProvider provideData()
     */
    public function testRenameAddToAddConfigForFirstService(SmartFileInfo $fixtureFileInfo): void
    {
        $this->markTestSkipped('Skipped - we need regex for checking only one service');

        $this->methodCallRenameCollector->addMethodCallRename(
            new MethodCallRename(FirstService::class, 'add', 'addConfig')
        );

        $result = $this->neonMethodCallRenamer->process($fixtureFileInfo);

        $expected = "services:
    firstService:
        factory: Rector\Core\Tests\NonPhpFile\NeonMethodCallRenamer\Source\FirstService
        setup:
            - addConfig('key1', 'value1')
            - addConfig('key2', 'value2')

    -
        class: Rector\Core\Tests\NonPhpFile\NeonMethodCallRenamer\Source\SecondService('service-name')
        setup:
            - add('first-key', 'first-value')
            - add('second-key', 'second-value')
";

        $this->assertEquals($expected, $result->getNewContent());
    }

    /**
     * @dataProvider provideData()
     */
    public function testRenameAddToAddConfigForSecondService(SmartFileInfo $fixtureFileInfo): void
    {
        $this->methodCallRenameCollector->addMethodCallRename(
            new MethodCallRename(SecondService::class, 'add', 'addConfig')
        );

        $result = $this->neonMethodCallRenamer->process($fixtureFileInfo);

        $expected = "services:
    firstService:
        factory: Rector\Core\Tests\NonPhpFile\NeonMethodCallRenamer\Source\FirstService
        setup:
            - add('key1', 'value1')
            - add('key2', 'value2')

    -
        class: Rector\Core\Tests\NonPhpFile\NeonMethodCallRenamer\Source\SecondService('service-name')
        setup:
            - addConfig('first-key', 'first-value')
            - addConfig('second-key', 'second-value')
";

        $this->assertEquals($expected, $result->getNewContent());
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

        $result = $this->neonMethodCallRenamer->process($fixtureFileInfo);

        $expected = "services:
    firstService:
        factory: Rector\Core\Tests\NonPhpFile\NeonMethodCallRenamer\Source\FirstService
        setup:
            - addConfig('key1', 'value1')
            - addConfig('key2', 'value2')

    -
        class: Rector\Core\Tests\NonPhpFile\NeonMethodCallRenamer\Source\SecondService('service-name')
        setup:
            - addConfig('first-key', 'first-value')
            - addConfig('second-key', 'second-value')
";

        $this->assertEquals($expected, $result->getNewContent());
    }

    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*.neon');
    }
}
