<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver;

use Iterator;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Rector\NodeTypeResolver\Tests\Source\AnotherClass;

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
        # on self call
        yield [__DIR__ . '/MethodCallSource/OnSelfCall.php.inc', 0, ['SomeParentCallingClass', AnotherClass::class]];
        yield [__DIR__ . '/MethodCallSource/OnSelfCall.php.inc', 1, ['SomeParentCallingClass', AnotherClass::class]];
        # on method call
        yield [__DIR__ . '/MethodCallSource/OnMethodCallCall.php.inc', 0, [
            'Rector\NodeTypeResolver\Tests\Source\AnotherClass',
        ]];
        # on variable call
        yield [__DIR__ . '/MethodCallSource/OnVariableCall.php.inc', 0, [
            'Rector\NodeTypeResolver\Tests\Source\SomeClass',
        ]];
        # on property call
        yield [__DIR__ . '/MethodCallSource/OnPropertyCall.php.inc', 0, [
            'Rector\NodeTypeResolver\Tests\Source\SomeClass',
        ]];
        # on magic class call
        yield [__DIR__ . '/MethodCallSource/OnMagicClassCall.php.inc', 0, [
            'Rector\NodeTypeResolver\Tests\Source\SomeClass',
        ]];
        # on trait method call
        yield [__DIR__ . '/MethodCallSource/SomeClassWithTraitCall.php.inc', 0, [
            'Rector\NodeTypeResolver\Tests\Source\SomeClass',
        ]];
    }
}
