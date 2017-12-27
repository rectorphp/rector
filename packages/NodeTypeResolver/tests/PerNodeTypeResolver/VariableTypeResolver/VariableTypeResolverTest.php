<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver;

use PhpParser\Node\Expr\Variable;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class VariableTypeResolverTest extends AbstractNodeTypeResolverTest
{
    public function testThis(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/This.php.inc', Variable::class);

        $this->assertSame(
            ['SomeNamespace\SomeClass', 'SomeNamespace\AnotherClass'],
            $this->nodeTypeResolver->resolve($variableNodes[0])
        );
    }

    public function testNew(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/SomeClass.php.inc', Variable::class);

        $this->assertSame(
            ['SomeNamespace\AnotherType'],
            $this->nodeTypeResolver->resolve($variableNodes[0])
        );
        $this->assertSame(
            ['SomeNamespace\AnotherType'],
            $this->nodeTypeResolver->resolve($variableNodes[2])
        );
    }

    public function testAssign(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/SomeClass.php.inc', Variable::class);

        $this->assertSame(
            ['SomeNamespace\AnotherType'],
            $this->nodeTypeResolver->resolve($variableNodes[1])
        );
    }

    public function testCallbackArgumentTypehint(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/ArgumentTypehint.php.inc', Variable::class);

        $this->assertSame(
            ['SomeNamespace\UseUse'],
            $this->nodeTypeResolver->resolve($variableNodes[0])
        );
        $this->assertSame(
            ['SomeNamespace\UseUse'],
            $this->nodeTypeResolver->resolve($variableNodes[1])
        );
    }
}
