<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeCallerTypeResolver;

use PhpParser\Node\Expr\MethodCall;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class Test extends AbstractNodeTypeResolverTest
{
    public function testOnPropertyCall()
    {
        $methodCallNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/OnPropertyCall.php.inc', MethodCall::class);

        dump($methodCallNodes);
        die;
    }
}
