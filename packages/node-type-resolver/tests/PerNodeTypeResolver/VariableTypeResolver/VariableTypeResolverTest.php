<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver;

use Iterator;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Fixture\AnotherType;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\Fixture\ThisClass;
use ReflectionClass;

/**
 * @see \Rector\NodeTypeResolver\NodeTypeResolver\VariableTypeResolver
 */
final class VariableTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file, int $nodePosition, TypeWithClassName $expectedTypeWithClassName): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Variable::class);

        $resolvedType = $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]);
        $this->assertInstanceOf(TypeWithClassName::class, $resolvedType);

        /** @var TypeWithClassName $resolvedType */
        $this->assertEquals($expectedTypeWithClassName->getClassName(), $resolvedType->getClassName());
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/ThisClass.php', 0, new ThisType(new ReflectionClass(ThisClass::class))];

        $anotherTypeObjectType = new ObjectType(AnotherType::class);

        yield [__DIR__ . '/Fixture/NewClass.php', 1, $anotherTypeObjectType];
        yield [__DIR__ . '/Fixture/NewClass.php', 3, $anotherTypeObjectType];
        yield [__DIR__ . '/Fixture/AssignmentClass.php', 2, $anotherTypeObjectType];
        yield [__DIR__ . '/Fixture/ArgumentTypehint.php', 1, $anotherTypeObjectType];
    }
}
