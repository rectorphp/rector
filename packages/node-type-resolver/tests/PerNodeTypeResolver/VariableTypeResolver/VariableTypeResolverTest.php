<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver;

use Iterator;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Source\AnotherType;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Source\ThisClass;
use Rector\NodeTypeResolver\Tests\Source\AnotherClass;
use Rector\StaticTypeMapper\TypeFactory\TypeFactoryStaticHelper;

/**
 * @see \Rector\NodeTypeResolver\NodeTypeResolver\VariableTypeResolver
 */
final class VariableTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file, int $nodePosition, Type $expectedType): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Variable::class);

        $resolvedType = $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]);
        $this->assertEquals($expectedType, $resolvedType);
    }

    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/Source/ThisClass.php',
            0,
            TypeFactoryStaticHelper::createUnionObjectType([ThisClass::class, AnotherClass::class]),
        ];

        $anotherTypeObjectType = new ObjectType(AnotherType::class);

        yield [__DIR__ . '/Source/NewClass.php', 1, $anotherTypeObjectType];
        yield [__DIR__ . '/Source/NewClass.php', 3, $anotherTypeObjectType];
        yield [__DIR__ . '/Source/AssignmentClass.php', 2, $anotherTypeObjectType];
        yield [__DIR__ . '/Source/ArgumentTypehint.php', 1, $anotherTypeObjectType];
    }
}
