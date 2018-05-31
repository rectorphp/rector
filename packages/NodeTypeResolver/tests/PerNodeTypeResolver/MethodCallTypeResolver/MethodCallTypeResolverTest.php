<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver;

use Iterator;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\MethodCallSource\OnSelfCall;
use Rector\NodeTypeResolver\Tests\Source\AnotherClass;
use Rector\NodeTypeResolver\Tests\Source\SomeClass;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\MethodCallTypeResolver
 */
final class MethodCallTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $position, array $expectedTypes): void
    {
        /** @var MethodCall[] $methodCallNodes */
        $methodCallNodes = $this->getNodesForFileOfType($file, MethodCall::class);
        $methodCallNode = $methodCallNodes[$position];

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($methodCallNode->var));
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/MethodCallSource/OnSelfCall.php', 0, [OnSelfCall::class, AnotherClass::class]];
        yield [__DIR__ . '/MethodCallSource/OnSelfCall.php', 1, [OnSelfCall::class, AnotherClass::class]];
        yield [__DIR__ . '/MethodCallSource/OnMethodCallCall.php', 0, [AnotherClass::class]];
        yield [__DIR__ . '/MethodCallSource/OnVariableCall.php', 0, [SomeClass::class]];
        yield [__DIR__ . '/MethodCallSource/OnPropertyCall.php', 0, [SomeClass::class]];
        yield [__DIR__ . '/MethodCallSource/OnMagicClassCall.php', 0, [SomeClass::class]];
        yield [__DIR__ . '/MethodCallSource/SomeClassWithTraitCall.php', 0, [SomeClass::class]];
    }
}
