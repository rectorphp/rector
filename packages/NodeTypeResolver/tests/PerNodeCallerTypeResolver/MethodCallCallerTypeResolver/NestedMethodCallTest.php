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
    private $formChainMethodCallNodes = [];

    /**
     * @var MethodCall[]
     */
    private $nestedMethodCallNodes = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->formChainMethodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/NestedMethodCallSource/FormChainMethodCalls.php.inc',
            MethodCall::class
        );

        $this->nestedMethodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/NestedMethodCallSource/NestedMethodCalls.php.inc',
            MethodCall::class
        );
    }

    /**
     * @dataProvider provideFormChainMethodCallData()
     * @param string[] $expectedTypes
     */
    public function testFormChainCalls(int $nodeId, string $methodName, array $expectedTypes): void
    {
        $node = $this->formChainMethodCallNodes[$nodeId];

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;
        $this->assertSame($methodName, $identifierNode->toString());

        $this->assertSame($expectedTypes, $this->nodeCallerTypeResolver->resolve($node));
    }

    /**
     * @return mixed[][]
     */
    public function provideFormChainMethodCallData(): array
    {
        return [
            [0, 'addRule', ['Nette\Forms\Rules']],
            [1, 'addCondition', [
                'Nette\Forms\Controls\TextInput',
                'Nette\Forms\Controls\TextBase',
                'Nette\Forms\Controls\BaseControl',
                'Nette\ComponentModel\Component',
            ]],
            [2, 'addText', [
                'Nette\Application\UI\Form',
                'Nette\ComponentModel\IComponent',
                'Nette\ComponentModel\IContainer',
                'ArrayAccess',
                'Nette\Utils\IHtmlString',
                'Nette\Application\UI\ISignalReceiver',
                'Nette\Forms\Form',
                'Nette\Forms\Container',
                'Nette\ComponentModel\Container',
                'Nette\ComponentModel\Component',
            ]],
        ];
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
