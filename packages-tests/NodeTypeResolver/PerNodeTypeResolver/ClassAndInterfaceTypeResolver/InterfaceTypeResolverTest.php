<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ClassAndInterfaceTypeResolver;

use Iterator;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ClassAndInterfaceTypeResolver\Source\SomeInterfaceWithParentInterface;

/**
 * @see \Rector\NodeTypeResolver\NodeTypeResolver\ClassAndInterfaceTypeResolver
 */
final class InterfaceTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider dataProvider()
     */
    public function test(string $file, int $nodePosition, TypeWithClassName $expectedTypeWithClassName): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Interface_::class);

        $resolvedType = $this->nodeTypeResolver->getType($variableNodes[$nodePosition]);
        $this->assertInstanceOf(TypeWithClassName::class, $resolvedType);

        /** @var TypeWithClassName $resolvedType */
        $this->assertEquals($expectedTypeWithClassName->getClassName(), $resolvedType->getClassName());
    }

    /**
     * @return Iterator<int[]|string[]|ObjectType[]>
     */
    public function dataProvider(): Iterator
    {
        yield [
            __DIR__ . '/Source/SomeInterfaceWithParentInterface.php',
            0,
            new ObjectType(SomeInterfaceWithParentInterface::class),
        ];
    }
}
