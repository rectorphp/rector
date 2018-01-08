<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver;

use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\ParamTypeResolver
 */
final class ParamTypeResolverTest extends AbstractNodeTypeResolverTest
{
    public function testTypehint(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/MethodParamTypeHint.php.inc', Variable::class);

        $this->assertSame(
            ['SomeNamespace\SubNamespace\Html'],
            $this->nodeTypeResolver->resolve($variableNodes[0])
        );
    }

    public function testDocBlock(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/MethodParamDocBlock.php.inc', Variable::class);

        $this->assertSame(
            ['SomeNamespace\SubNamespace\Html'],
            $this->nodeTypeResolver->resolve($variableNodes[0])
        );
    }
}
