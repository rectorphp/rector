<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeCallerTypeResolver;

use PhpParser\Node\Expr\MethodCall;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class Test extends AbstractNodeTypeResolverTest
{
    public function testOnVariableCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/OnVariableCall.php.inc', MethodCall::class);

        $methodCallNode = $methodCallNodes[0];
        $callerNodeTypes = $methodCallNode->getAttribute(Attribute::CALLER_TYPES);

        $this->assertSame(['Nette\DI\Container'], $callerNodeTypes);
    }

    public function testOnPropertyCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/OnPropertyCall.php.inc', MethodCall::class);

        $methodCallNode = $methodCallNodes[0];
        $callerNodeTypes = $methodCallNode->getAttribute(Attribute::CALLER_TYPES);

        $this->assertSame(['Nette\DI\Container'], $callerNodeTypes);
    }
}
