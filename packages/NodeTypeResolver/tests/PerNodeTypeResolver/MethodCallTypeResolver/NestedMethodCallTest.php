<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver;

use Iterator;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\NestedMethodCallSource\Application\UI\Form as ApplicationForm;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\NestedMethodCallSource\Form\Controls\TextArea;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\NestedMethodCallSource\Form\Controls\TextInput;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\NestedMethodCallSource\Form\Form;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\NestedMethodCallSource\Form\Rules;
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
        yield [__DIR__ . '/NestedMethodCallSource/FormChainMethodCalls.php', 0, 'addRule', [Rules::class]];

        yield [__DIR__ . '/NestedMethodCallSource/FormChainMethodCalls.php', 1, 'addCondition', [
            TextInput::class,
            TextArea::class,
        ]];

        yield [__DIR__ . '/NestedMethodCallSource/FormChainMethodCalls.php', 2, 'addText', [
            ApplicationForm::class,
            Form::class,
        ]];

        yield [__DIR__ . '/NestedMethodCallSource/OnMethodCallCallDifferentType.php', 0, 'getParameters', [
            AnotherClass::class,
        ]];

        yield [__DIR__ . '/NestedMethodCallSource/OnMethodCallCallDifferentType.php', 1, 'createAnotherClass', [
            ClassWithFluentNonSelfReturn::class,
        ]];

        yield [
            __DIR__ . '/NestedMethodCallSource/NestedMethodCalls.php',
            0,
            'getParameters',
            [AnotherClass::class],
        ];

        yield [
            __DIR__ . '/NestedMethodCallSource/NestedMethodCalls.php',
            1,
            'callAndReturnSelf',
            [AnotherClass::class],
        ];

        yield [__DIR__ . '/NestedMethodCallSource/NestedMethodCalls.php', 2, 'createAnotherClass', [
            ClassWithFluentNonSelfReturn::class,
        ]];
    }
}
