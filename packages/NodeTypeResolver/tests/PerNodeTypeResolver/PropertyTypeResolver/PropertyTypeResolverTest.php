<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver;

use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class PropertyTypeResolverTest extends AbstractNodeTypeResolverTest
{
    public function testDocBlock(): void
    {
        $propertyNodes = $this->getNodesForFileOfType(
            __DIR__ . '/Source/DocBlockDefinedProperty.php.inc',
            Property::class
        );

        $this->assertSame(
            ['SomeNamespace\PropertyType'],
            $this->nodeTypeResolver->resolve($propertyNodes[0])
        );
    }

    public function testConstructorType(): void
    {
        $propertyNodes = $this->getNodesForFileOfType(
            __DIR__ . '/Source/ConstructorDefinedProperty.php.inc',
            Property::class
        );

        $this->assertSame(
            ['SomeNamespace\PropertyType'],
            $this->nodeTypeResolver->resolve($propertyNodes[0])
        );
    }

    public function testPartialDocBlock(): void
    {
        $propertyNodes = $this->getNodesForFileOfType(
            __DIR__ . '/Source/PartialDocBlock.php.inc',
            Property::class
        );

        $this->assertSame([
            'PhpParser\Node\Stmt\ClassMethod',
            'PhpParser\Node\Stmt\Function_',
            'PhpParser\Node\Expr\Closure',
            'PhpParser\Node\Stmt',
            'PhpParser\NodeAbstract',
            'PhpParser\Node\Expr',
        ], $this->nodeTypeResolver->resolve($propertyNodes[0]));
    }
}
