<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeCallerTypeResolver\MethodCallCallerTypeResolver;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

final class NestedMethodCallTest extends AbstractNodeTypeResolverTest
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
        $methodCallNode = $this->formChainMethodCallNodes[$nodeId];

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNode->name;
        $this->assertSame($methodName, $identifierNode->toString());

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($methodCallNode->var));
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

    /**
     * @dataProvider provideNestedDifferentMethodCallData()
     * @param string[] $expectedTypes
     */
    public function testOnNestedDifferentMethodCall(int $position, string $methodName, array $expectedTypes): void
    {
        /** @var MethodCall[] $methodCallNodes */
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/NestedMethodCallSource/OnMethodCallCallDifferentType.php.inc',
            MethodCall::class
        );

        $methodCallNode = $methodCallNodes[$position];

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNode->name;
        $this->assertSame($methodName, $identifierNode->toString());

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($methodCallNode->var));
    }

    /**
     * @return mixed[][]
     */
    public function provideNestedDifferentMethodCallData(): array
    {
        return [
            [0, 'setScope', ['Symfony\Component\DependencyInjection\Definition']],
            [1, 'register', [
                'Symfony\Component\DependencyInjection\ContainerBuilder',
                'Symfony\Component\DependencyInjection\ResettableContainerInterface',
                'Symfony\Component\DependencyInjection\ContainerInterface',
                'Psr\Container\ContainerInterface',
                'Symfony\Component\DependencyInjection\TaggedContainerInterface',
                'Symfony\Component\DependencyInjection\Container',
            ]],
        ];
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

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($node->var));
    }

    /**
     * @return mixed[][]
     */
    public function provideNestedMethodCallData(): array
    {
        return [
            # nested method calls
            [0, 'getParameters', ['Nette\DI\Container']],
            [1, 'addService', ['Nette\DI\Container']],
            [2, 'createContainer', ['Nette\Config\Configurator', 'Nette\Object']],
        ];
    }
}
