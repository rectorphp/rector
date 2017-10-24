<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver;

use PhpParser\Node\Expr\Variable;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class Test extends AbstractNodeTypeResolverTest
{
    public function testDocBlock(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/MethodParam.php.inc', Variable::class);

        $this->assertSame('SomeNamespace\SubNamespace\Html', $variableNodes[0]->getAttribute(Attribute::TYPE));
    }
}
