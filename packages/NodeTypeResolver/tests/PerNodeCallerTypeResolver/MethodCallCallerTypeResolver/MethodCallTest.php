<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeCallerTypeResolver\MethodCallCallerTypeResolver;

use PhpParser\Node\Expr\MethodCall;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\PerNodeCallerTypeResolver\AbstractNodeCallerTypeResolverTest;

final class MethodCallTest extends AbstractNodeCallerTypeResolverTest
{
    public function testOnSelfCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/MethodCallSource/OnSelfCall.php.inc',
            MethodCall::class
        );

        $this->assertSame([
            'SomeClass',
            'Nette\Config\Configurator',
            'Nette\Object',
        ], $this->nodeCallerTypeResolver->resolve($methodCallNodes[0]));

        $this->assertSame([
            'SomeClass',
            'Nette\Config\Configurator',
            'Nette\Object',
        ], $this->nodeCallerTypeResolver->resolve($methodCallNodes[1]));
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

        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, [
            'Nette\Config\Configurator',
            'Nette\Object',
        ]);
    }

    public function testOnPropertyCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/MethodCallSource/OnPropertyCall.php.inc',
            MethodCall::class
        );

        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, [
            'Nette\Config\Configurator',
            'Nette\Object',
        ]);
    }
}
