<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeCallerTypeResolver;

use PhpParser\Node\Expr\MethodCall;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class MethodCallTest extends AbstractNodeTypeResolverTest
{
    public function testOnNestedMethodCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/MethodCallSource/NestedMethodCalls.php.inc',
            MethodCall::class
        );

        $this->assertCount(3, $methodCallNodes);

        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, ['Nette\DI\Container']);
        $this->doTestAttributeEquals($methodCallNodes[1], Attribute::CALLER_TYPES, ['Nette\DI\Container']);
        $this->doTestAttributeEquals($methodCallNodes[2], Attribute::CALLER_TYPES, ['Nette\DI\Container']);
    }

    public function testOnSelfCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/MethodCallSource/OnSelfCall.php.inc',
            MethodCall::class
        );

        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, null);
        $this->doTestAttributeEquals($methodCallNodes[1], Attribute::CALLER_TYPES, [
            'SomeClass',
            'Nette\Config\Configurator',
            'Nette\Object',
        ]);
    }

    public function testOnMethodCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/MethodCallSource/OnMethodCallCall.php.inc',
            MethodCall::class
        );

        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, ['Nette\DI\Container']);
    }

    public function testOnVariableCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/MethodCallSource/OnVariableCall.php.inc',
            MethodCall::class
        );

        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, ['Nette\DI\Container']);
    }

    public function testOnPropertyCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/MethodCallSource/OnPropertyCall.php.inc',
            MethodCall::class
        );

        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, ['Nette\DI\Container']);
    }
}
