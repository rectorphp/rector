<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver;

use PhpParser\Node\Expr\Variable;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class Test extends AbstractNodeTypeResolverTest
{
    public function testNewAndAssign(): void
    {
        $nodes = $this->getNodesWithTypesForFile(__DIR__ . '/Source/SomeClass.php.inc');
        $variableNodes = $this->nodeFinder->findInstanceOf($nodes, Variable::class);

        $this->assertSame('SomeNamespace\AnotherType', $variableNodes[0]->getAttribute(Attribute::TYPE));
        $this->assertSame('SomeNamespace\AnotherType', $variableNodes[1]->getAttribute(Attribute::TYPE));
        $this->assertSame('SomeNamespace\AnotherType', $variableNodes[2]->getAttribute(Attribute::TYPE));
    }

    public function testCallbackArgumentTypehint(): void
    {
        $nodes = $this->getNodesWithTypesForFile(__DIR__ . '/Source/callbackArgumentTypehint.php.inc');
        $variableNodes = $this->nodeFinder->findInstanceOf($nodes, Variable::class);

        $this->assertSame('SomeNamespace\UseUse', $variableNodes[0]->getAttribute(Attribute::TYPE));
        $this->assertSame('SomeNamespace\UseUse', $variableNodes[1]->getAttribute(Attribute::TYPE));
    }
}
