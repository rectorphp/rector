<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver;

use Iterator;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\Source\AnotherClass;
use Rector\NodeTypeResolver\Tests\Source\ClassWithFluentNonSelfReturn;

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

    public function provideData(): Iterator
    {
        # form chain method calls
        yield [
            __DIR__ . '/NestedMethodCallSource/FormChainMethodCalls.php.inc', 0, 'addRule', [
                'Stub_Nette\Forms\Rules',
            ],
        ];
        yield [__DIR__ . '/NestedMethodCallSource/FormChainMethodCalls.php.inc', 1, 'addCondition', [
            'Stub_Nette\Forms\Controls\TextInput',
            'Stub_Nette\Forms\Controls\TextArea',
        ]];
        yield [__DIR__ . '/NestedMethodCallSource/FormChainMethodCalls.php.inc', 2, 'addText', [
            'Stub_Nette\Application\UI\Form',
            'Stub_Nette\Forms\Form',
        ]];
        # nested different method calls
        yield [__DIR__ . '/NestedMethodCallSource/OnMethodCallCallDifferentType.php.inc', 0, 'getParameters', [
            AnotherClass::class,
        ]];
        yield [__DIR__ . '/NestedMethodCallSource/OnMethodCallCallDifferentType.php.inc', 1, 'createAnotherClass', [
            ClassWithFluentNonSelfReturn::class,
        ]];
        # nested method calls
        yield [
            __DIR__ . '/NestedMethodCallSource/NestedMethodCalls.php.inc',
            0,
            'getParameters',
            [AnotherClass::class],
        ];
        yield [
            __DIR__ . '/NestedMethodCallSource/NestedMethodCalls.php.inc',
            1,
            'callAndReturnSelf',
            [AnotherClass::class],
        ];
        # nested method calls
        yield [__DIR__ . '/NestedMethodCallSource/NestedMethodCalls.php.inc', 2, 'createAnotherClass', [
            ClassWithFluentNonSelfReturn::class,
        ]];
    }
}
