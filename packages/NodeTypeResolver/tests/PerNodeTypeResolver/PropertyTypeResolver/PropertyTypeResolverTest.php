<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver;

use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\PropertyTypeResolver
 */
final class PropertyTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideTypeForNodesAndFilesData()
     * @param string[]
     */
    public function test(string $file, int $nodePosition, array $expectedTypes): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Property::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]));
    }

    /**
     * @return mixed[][]
     */
    public function provideTypeForNodesAndFilesData(): array
    {
        return [
            # doc block
            [__DIR__ . '/Source/DocBlockDefinedProperty.php.inc', 0, ['SomeNamespace\PropertyType']],
            # constructor defined property
            [__DIR__ . '/Source/ConstructorDefinedProperty.php.inc', 0, ['SomeNamespace\PropertyType']],
            # partial doc block
            [__DIR__ . '/Source/PartialDocBlock.php.inc', 0, [
                'PhpParser\Node\Stmt\ClassMethod',
                'PhpParser\Node\Stmt\Function_',
                'PhpParser\Node\Expr\Closure',
                'PhpParser\Node\Stmt',
                'PhpParser\NodeAbstract',
                'PhpParser\Node\Expr',
            ]],
        ];
    }
}
