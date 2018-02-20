<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\MethodCallTypeResolver
 */
final class NestedMethodCallTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $nodePosition, string $methodName, array $expectedTypes): void
    {
        /** @var MethodCall[] $methodCallNodes */
        $methodCallNodes = $this->getNodesForFileOfType($file, MethodCall::class);

        $methodCallNode = $methodCallNodes[$nodePosition];

        /** @var Identifier $identifierNode */
        $identifierNode = $methodCallNode->name;
        $this->assertSame($methodName, $identifierNode->toString());

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($methodCallNode->var));
    }

    /**
     * @return mixed[][]
     */
    public function provideData(): array
    {
        return [
            # form chain method calls
            [__DIR__ . '/NestedMethodCallSource/FormChainMethodCalls.php.inc', 0, 'addRule', ['Nette\Forms\Rules']],
            [__DIR__ . '/NestedMethodCallSource/FormChainMethodCalls.php.inc', 1, 'addCondition', [
                'Nette\Forms\Controls\TextInput',
                'Nette\Forms\Controls\TextBase',
                'Nette\Forms\Controls\BaseControl',
                'Nette\ComponentModel\Component',
            ]],
            [__DIR__ . '/NestedMethodCallSource/FormChainMethodCalls.php.inc', 2, 'addText', [
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

            # nested different method calls
            [__DIR__ . '/NestedMethodCallSource/OnMethodCallCallDifferentType.php.inc', 0, 'setScope', [
                'Symfony\Component\DependencyInjection\Definition',
            ]],
            [__DIR__ . '/NestedMethodCallSource/OnMethodCallCallDifferentType.php.inc', 1, 'register', [
                'Symfony\Component\DependencyInjection\ContainerBuilder',
                'Symfony\Component\DependencyInjection\ResettableContainerInterface',
                'Symfony\Component\DependencyInjection\ContainerInterface',
                'Psr\Container\ContainerInterface',
                'Symfony\Component\DependencyInjection\TaggedContainerInterface',
                'Symfony\Component\DependencyInjection\Container',
            ]],

            # nested method calls
            [__DIR__ . '/NestedMethodCallSource/NestedMethodCalls.php.inc', 0, 'getParameters', ['Nette\DI\Container']],
            [__DIR__ . '/NestedMethodCallSource/NestedMethodCalls.php.inc', 1, 'addService', ['Nette\DI\Container']],
            [__DIR__ . '/NestedMethodCallSource/NestedMethodCalls.php.inc', 2, 'createContainer', [
                'Nette\Config\Configurator', 'Nette\Object',
            ]],
        ];
    }
}
