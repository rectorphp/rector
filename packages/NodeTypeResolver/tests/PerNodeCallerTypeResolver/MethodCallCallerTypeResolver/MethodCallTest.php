<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeCallerTypeResolver\MethodCallCallerTypeResolver;

use PhpParser\Node\Expr\MethodCall;
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

        $this->assertSame([
            'Nette\DI\Container',
        ], $this->nodeCallerTypeResolver->resolve($methodCallNodes[0]));
    }

    public function testOnVariableCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/MethodCallSource/OnVariableCall.php.inc',
            MethodCall::class
        );

        $this->assertSame([
            'Nette\Config\Configurator',
            'Nette\Object',
        ], $this->nodeCallerTypeResolver->resolve($methodCallNodes[0]));
    }

    public function testOnPropertyCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/MethodCallSource/OnPropertyCall.php.inc',
            MethodCall::class
        );

        $this->assertSame([
            'Nette\Config\Configurator',
            'Nette\Object',
        ], $this->nodeCallerTypeResolver->resolve($methodCallNodes[0]));
    }
}
