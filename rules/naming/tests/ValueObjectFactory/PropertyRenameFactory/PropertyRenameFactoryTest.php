<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\ValueObjectFactory\PropertyRenameFactory;

use Iterator;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\Naming\ExpectedNameResolver\MatchPropertyTypeExpectedNameResolver;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\Naming\ValueObjectFactory\PropertyRenameFactory;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PropertyRenameFactoryTest extends AbstractKernelTestCase
{
    /**
     * @var PropertyRenameFactory
     */
    private $propertyRenameFactory;

    /**
     * @var FileInfoParser
     */
    private $fileInfoParser;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var MatchPropertyTypeExpectedNameResolver
     */
    private $matchPropertyTypeExpectedNameResolver;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->propertyRenameFactory = self::$container->get(PropertyRenameFactory::class);
        $this->matchPropertyTypeExpectedNameResolver = self::$container->get(
            MatchPropertyTypeExpectedNameResolver::class
        );

        $this->fileInfoParser = self::$container->get(FileInfoParser::class);
        $this->betterNodeFinder = self::$container->get(BetterNodeFinder::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfoWithProperty, string $expectedName, string $currentName): void
    {
        $property = $this->getPropertyFromFileInfo($fileInfoWithProperty);

        $actualPropertyRename = $this->propertyRenameFactory->create(
            $property,
            $this->matchPropertyTypeExpectedNameResolver
        );
        $this->assertNotNull($actualPropertyRename);

        /** @var PropertyRename $actualPropertyRename */
        $this->assertSame($property, $actualPropertyRename->getProperty());
        $this->assertSame($expectedName, $actualPropertyRename->getExpectedName());
        $this->assertSame($currentName, $actualPropertyRename->getCurrentName());
    }

    public function provideData(): Iterator
    {
        yield [new SmartFileInfo(__DIR__ . '/Fixture/SomeClass.php.inc'), 'eliteManager', 'eventManager'];
    }

    private function getPropertyFromFileInfo(SmartFileInfo $fileInfo): Property
    {
        $nodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorate($fileInfo);

        /** @var Property|null $property */
        $property = $this->betterNodeFinder->findFirstInstanceOf($nodes, Property::class);
        if ($property === null) {
            throw new ShouldNotHappenException();
        }

        return $property;
    }
}
