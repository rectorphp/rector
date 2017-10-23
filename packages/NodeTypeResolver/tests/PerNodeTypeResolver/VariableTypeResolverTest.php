<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver;

use PhpParser\Node\Expr\Variable;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class VariableTypeResolverTest extends AbstractNodeTypeResolverTest
{
    public function test(): void
    {
        $nodes = $this->getNodesWithTypesForFile(__DIR__ . '/Source/SomeClass.php.inc');
        $variableNodes = $this->nodeFinder->findInstanceOf($nodes, Variable::class);

        $this->assertSame('SomeNamespace\AnotherType', $variableNodes[0]->getAttribute(Attribute::TYPE));
        $this->assertSame('SomeNamespace\AnotherType', $variableNodes[1]->getAttribute(Attribute::TYPE));
        $this->assertSame('SomeNamespace\AnotherType', $variableNodes[2]->getAttribute(Attribute::TYPE));
    }
}
