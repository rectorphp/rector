<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\ValueObjectFactory\PropertyRenameFactory;

use Iterator;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\FileSystemRector\Parser\FileInfoParser;
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

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->bootKernel(RectorKernel::class);

        $this->propertyRenameFactory = self::$container->get(PropertyRenameFactory::class);
        $this->fileInfoParser = self::$container->get(FileInfoParser::class);
        $this->betterNodeFinder = self::$container->get(BetterNodeFinder::class);
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(Property $property, string $expectedName, string $currentName): void
    {
        /** @var PropertyRename $actualPropertyRename */
        $actualPropertyRename = $this->propertyRenameFactory->create($property);

        $this->assertSame($property, $actualPropertyRename->getProperty());
        $this->assertSame($expectedName, $actualPropertyRename->getExpectedName());
        $this->assertSame($currentName, $actualPropertyRename->getCurrentName());
        $this->assertInstanceOf(PropertyProperty::class, $actualPropertyRename->getPropertyProperty());
        $this->assertInstanceOf(ClassLike::class, $actualPropertyRename->getClassLike());
    }

    public function createDataProvider(): Iterator
    {
        yield [$this->getPropertyFromFile(__DIR__ . '/Fixture/SomeClass.php'), 'eliteManager', 'eventManager'];
    }

    private function getPropertyFromFile(string $string): Property
    {
        $smartFileInfo = new SmartFileInfo($string);
        $nodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorate($smartFileInfo);
        /** @var Property $property */
        $property = $this->betterNodeFinder->findFirstInstanceOf($nodes, Property::class);
        return $property;
    }
}
