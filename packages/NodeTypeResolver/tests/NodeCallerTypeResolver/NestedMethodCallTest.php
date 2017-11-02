<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeCallerTypeResolver;

use PhpParser\Node\Expr\MethodCall;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class NestedMethodCallTest extends AbstractNodeTypeResolverTest
{
    public function testFormChainCalls(): void
    {
        /** @var MethodCall[] $methodCallNodes */
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/NestedMethodCallSource/FormChainMethodCalls.php.inc',
            MethodCall::class
        );

        $this->assertCount(4, $methodCallNodes);

        $this->assertSame('addRule', $methodCallNodes[0]->name->toString());
        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, [null]);

        $this->assertSame('addRule', $methodCallNodes[1]->name->toString());
        $this->doTestAttributeEquals($methodCallNodes[1], Attribute::CALLER_TYPES, [null]);

        $this->assertSame('addCondition', $methodCallNodes[2]->name->toString());
        $this->doTestAttributeEquals($methodCallNodes[2], Attribute::CALLER_TYPES, [null]);

        $this->assertSame('addText', $methodCallNodes[3]->name->toString());
        $this->assertContains(
            'Nette\Application\UI\Form',
            $methodCallNodes[3]->getAttribute(Attribute::CALLER_TYPES)
        );
    }

    public function testOnNestedDifferentMethodCall(): void
    {
        /** @var MethodCall[] $methodCallNodes */
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/NestedMethodCallSource/OnMethodCallCallDifferentType.php.inc',
            MethodCall::class
        );

        $this->assertCount(2, $methodCallNodes);
        $this->assertSame('setScope', $methodCallNodes[0]->name->toString());
        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, [
            'Symfony\Component\DependencyInjection\Definition',
        ]);

        $this->assertSame('register', $methodCallNodes[1]->name->toString());
        $this->doTestAttributeEquals($methodCallNodes[1], Attribute::CALLER_TYPES, [
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            'Symfony\Component\DependencyInjection\ResettableContainerInterface',
            'Symfony\Component\DependencyInjection\ContainerInterface',
            'Psr\Container\ContainerInterface',
            'Symfony\Component\DependencyInjection\TaggedContainerInterface',
            'Symfony\Component\DependencyInjection\Container',
        ]);
    }

    public function testOnNestedMethodCall(): void
    {
        /** @var MethodCall[] $methodCallNodes */
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/NestedMethodCallSource/NestedMethodCalls.php.inc',
            MethodCall::class
        );

        $this->assertCount(3, $methodCallNodes);

        $this->assertSame('getParameters', $methodCallNodes[0]->name->toString());
        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, [
            'Nette\DI\Container',
        ]);

        $this->assertSame('addService', $methodCallNodes[1]->name->toString());
        $this->doTestAttributeEquals($methodCallNodes[1], Attribute::CALLER_TYPES, [
            'Nette\DI\Container',
        ]);

        $this->assertSame('createContainer', $methodCallNodes[2]->name->toString());
        $this->doTestAttributeEquals($methodCallNodes[2], Attribute::CALLER_TYPES, [
            'Nette\Config\Configurator',
            'Nette\Object',
        ]);
    }
}
