<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;

use Iterator;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\Testing\TestingParser\TestingParser;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ResolveTagToKnownFullyQualifiedNameTest extends AbstractTestCase
{
    private ClassAnnotationMatcher $classAnnotationMatcher;

    private TestingParser $testingParser;

    private BetterNodeFinder $nodeFinder;

    private PhpDocInfoFactory $phpDocInfoFactory;

    private NodeNameResolver $nodeNameResolver;

    protected function setUp(): void
    {
        $this->boot();

        $this->classAnnotationMatcher = $this->getService(ClassAnnotationMatcher::class);
        $this->testingParser = $this->getService(TestingParser::class);
        $this->nodeFinder = $this->getService(BetterNodeFinder::class);
        $this->phpDocInfoFactory = $this->getService(PhpDocInfoFactory::class);
        $this->nodeNameResolver = $this->getService(NodeNameResolver::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function testResolvesClass(SmartFileInfo $file): void
    {
        $nodes = $this->testingParser->parseFileToDecoratedNodes($file->getRelativeFilePath());
        $properties = $this->nodeFinder->findInstancesOf($nodes, [Property::class]);

        foreach ($properties as $property) {
            /** @var Property $property */
            $phpDoc = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            /** @var VarTagValueNode $varTag */
            $varTag = $phpDoc->getByType(VarTagValueNode::class)[0];
            $value = $varTag->type->__toString();
            $propertyName = strtolower($this->nodeNameResolver->getName($property));

            $result = $this->classAnnotationMatcher->resolveTagToKnownFullyQualifiedName($value, $property);
            if (str_starts_with($propertyName, 'unknown')) {
                $this->assertNull($result);
            } elseif (str_contains($propertyName, 'aliased')) {
                $unaliasedClass = str_replace('Aliased', '', $value);
                $this->assertStringEndsWith($unaliasedClass, $result ?? '');
            } elseif (str_starts_with($propertyName, 'known')) {
                $this->assertStringEndsWith($value, $result ?? '');
            } else {
                throw new ShouldNotHappenException('All Variables should start with "known" or "unknown"!');
            }
        }
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        $directory = __DIR__ . '/Fixture';
        return StaticFixtureFinder::yieldDirectory($directory);
    }
}
