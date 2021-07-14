<?php

declare(strict_types=1);

namespace Rector\Core\Tests\NodeFactory\ClassWithPublicPropertiesFactory;

use Iterator;
use Rector\Core\NodeFactory\ClassWithPublicPropertiesFactory;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Core\NodeFactory\ClassWithPublicPropertiesFactory
 */
final class ClassWithPublicPropertiesFactoryTest extends AbstractTestCase
{
    private ClassWithPublicPropertiesFactory $classWithPublicPropertiesFactory;

    private BetterStandardPrinter $betterStandardPrinter;

    protected function setUp(): void
    {
        $this->bootFromConfigFileInfos([new SmartFileInfo(__DIR__ . '/../../../config/config.php')]);
        $this->classWithPublicPropertiesFactory = $this->getService(ClassWithPublicPropertiesFactory::class);
        $this->betterStandardPrinter = $this->getService(BetterStandardPrinter::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fixtureFileInfo): void
    {
        $fixtureContent = $fixtureFileInfo->getContents();
        [$content, $expected] = explode("-----\n", $fixtureContent, 2);

        $classSettings = json_decode($content, true);
        $node = $this->classWithPublicPropertiesFactory->createNode(
            $classSettings['fullyQualifiedName'],
            $classSettings['properties'],
            $classSettings['parent'] ?? null,
            $classSettings['traits'] ?? []
        );

        $output = "<?php\n\n" . $this->betterStandardPrinter->print($node) . "\n";
        $this->assertSame($expected, $output);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively(__DIR__ . '/Fixture');
    }
}
