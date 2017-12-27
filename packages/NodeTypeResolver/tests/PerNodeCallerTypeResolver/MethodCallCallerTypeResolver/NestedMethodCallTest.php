<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeCallerTypeResolver\MethodCallCallerTypeResolver;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\NodeTypeResolver\Tests\PerNodeCallerTypeResolver\AbstractNodeCallerTypeResolverTest;

final class NestedMethodCallTest extends AbstractNodeCallerTypeResolverTest
{
    /**
     * @var MethodCall[]
     */
    private $nestedMethodCallNodes = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->nestedMethodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/NestedMethodCallSource/NestedMethodCalls.php.inc',
            MethodCall::class
        );
    }


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
            $this->nodeCallerTypeResolver->resolve($methodCallNodes[0])
        );

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNodes[1]->name;
        $this->assertSame('addCondition', $identifierNode->toString());
        $this->assertContains(
            'Nette\Forms\Controls\TextInput',
            $this->nodeCallerTypeResolver->resolve($methodCallNodes[1])
        );

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNodes[2]->name;
        $this->assertSame('addText', $identifierNode->toString());
        $this->assertContains(
            'Nette\Application\UI\Form',
            $this->nodeCallerTypeResolver->resolve($methodCallNodes[2])
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

        $this->assertSame(
            ['Symfony\Component\DependencyInjection\Definition'],
            $this->nodeCallerTypeResolver->resolve($methodCallNodes[0])
        );

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNodes[1]->name;
        $this->assertSame('register', $identifierNode->toString());

        $this->assertSame([
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            'Symfony\Component\DependencyInjection\ResettableContainerInterface',
            'Symfony\Component\DependencyInjection\ContainerInterface',
            'Psr\Container\ContainerInterface',
            'Symfony\Component\DependencyInjection\TaggedContainerInterface',
            'Symfony\Component\DependencyInjection\Container',
        ], $this->nodeCallerTypeResolver->resolve($methodCallNodes[1]));
    }

    /**
     * @dataProvider provideNestedMethodCallData()
     * @param string[] $expectedTypes
     */
    public function testOnNestedMethodCall(int $nodeId, string $methodName, array $expectedTypes): void
    {
        $node = $this->nestedMethodCallNodes[$nodeId];

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;
        $this->assertSame($methodName, $identifierNode->toString());

        $this->assertSame($expectedTypes, $this->nodeCallerTypeResolver->resolve($node));
    }

    /**
     * @return mixed[][]
     */
    public function provideNestedMethodCallData(): array
    {
        return [
            [0, 'getParameters', ['Nette\DI\Container']],
            [1, 'addService', ['Nette\DI\Container']],
            [2, 'createContainer', ['Nette\Config\Configurator', 'Nette\Object']],
        ];
    }


}
