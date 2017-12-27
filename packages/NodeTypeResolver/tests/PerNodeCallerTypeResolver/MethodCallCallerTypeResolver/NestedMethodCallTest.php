<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeCallerTypeResolver\MethodCallCallerTypeResolver;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\PerNodeCallerTypeResolver\AbstractNodeCallerTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

final class NestedMethodCallTest extends AbstractNodeCallerTypeResolverTest
{
    public function testFormChainCalls(): void
    {
        /** @var MethodCall[] $methodCallNodes */
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/NestedMethodCallSource/FormChainMethodCalls.php.inc',
            MethodCall::class
        );

        $this->assertCount(3, $methodCallNodes);

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNodes[0]->name;
        $this->assertSame('addRule', $identifierNode->toString());
        $this->assertSame(
            ['Nette\Forms\Rules'],
            $methodCallNodes[0]->getAttribute(Attribute::CALLER_TYPES)
        );

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNodes[1]->name;
        $this->assertSame('addCondition', $identifierNode->toString());
        $this->assertContains(
            'Nette\Forms\Controls\TextInput',
            $methodCallNodes[1]->getAttribute(Attribute::CALLER_TYPES)
        );

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNodes[2]->name;
        $this->assertSame('addText', $identifierNode->toString());
        $this->assertContains(
            'Nette\Application\UI\Form',
            $methodCallNodes[2]->getAttribute(Attribute::CALLER_TYPES)
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

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNodes[0]->name;
        $this->assertSame('setScope', $identifierNode->toString());
        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, [
            'Symfony\Component\DependencyInjection\Definition',
        ]);

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNodes[1]->name;
        $this->assertSame('register', $identifierNode->toString());
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

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNodes[0]->name;
        $this->assertSame('getParameters', $identifierNode->toString());
        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, [
            'Nette\DI\Container',
        ]);

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNodes[1]->name;
        $this->assertSame('addService', $identifierNode->toString());
        $this->doTestAttributeEquals($methodCallNodes[1], Attribute::CALLER_TYPES, [
            'Nette\DI\Container',
        ]);

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNodes[2]->name;
        $this->assertSame('createContainer', $identifierNode->toString());
        $this->doTestAttributeEquals($methodCallNodes[2], Attribute::CALLER_TYPES, [
            'Nette\Config\Configurator',
            'Nette\Object',
        ]);
    }
}
