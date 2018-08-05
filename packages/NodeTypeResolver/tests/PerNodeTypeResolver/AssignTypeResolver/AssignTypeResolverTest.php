<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AssignTypeResolver;

use Iterator;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\ScopeToTypesResolver;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AssignTypeResolver\Source\ClassWithInterface;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AssignTypeResolver\Source\ClassWithParent;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AssignTypeResolver\Source\ParentClass;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AssignTypeResolver\Source\ParentInterface;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\AssignTypeResolver
 */
final class AssignTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideTypeForNodesAndFilesData()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $nodePosition, array $expectedTypes): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Variable::class);

        /** @var ScopeToTypesResolver $scopeToTypesResolver */
        $scopeToTypesResolver = $this->container->get(ScopeToTypesResolver::class);

//        $variableNodeType = $scopeToTypesResolver->resolveScopeToTypes($variableNodes[$nodePosition]);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]));
    }

    public function provideTypeForNodesAndFilesData(): Iterator
    {
        yield [__DIR__ . '/Source/New.php', 0, [ClassWithInterface::class, ParentInterface::class]];
        yield [__DIR__ . '/Source/MethodCall.php', 0, [ClassWithParent::class, ParentClass::class]];
//        yield [__DIR__ . '/Source/MethodCall.php', 2, [ClassWithParent::class, ParentClass::class]];
//        yield [__DIR__ . '/Source/PropertyFetch.php', 0, [ClassWithParent::class, ParentClass::class]];
//        yield [__DIR__ . '/Source/PropertyFetch.php', 2, [ClassWithParent::class, ParentClass::class]];
//        yield [__DIR__ . '/Source/ClassConstant.php', 0, [ClassWithParent::class, ParentClass::class]];
//        yield [__DIR__ . '/Source/ClassConstant.php', 2, [ClassWithParent::class, ParentClass::class]];
    }
}
