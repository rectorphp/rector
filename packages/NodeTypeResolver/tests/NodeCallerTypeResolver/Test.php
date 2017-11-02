<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeCallerTypeResolver;

use PhpParser\Node\Expr\MethodCall;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class Test extends AbstractNodeTypeResolverTest
{
    public function testOnMethodCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/OnMethodCallCall.php.inc', MethodCall::class);

        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, ['Nette\DI\Container']);
    }

//    public function testOnVariableCall(): void
//    {
//        $methodCallNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/OnVariableCall.php.inc', MethodCall::class);
//
//        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, ['Nette\DI\Container']);
//    }
//
//    public function testOnPropertyCall(): void
//    {
//        $methodCallNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/OnPropertyCall.php.inc', MethodCall::class);
//
//        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, ['Nette\DI\Container']);
//    }
}
