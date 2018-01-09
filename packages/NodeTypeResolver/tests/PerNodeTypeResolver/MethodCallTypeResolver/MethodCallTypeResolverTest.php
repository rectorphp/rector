<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver;

use PhpParser\Node\Expr\MethodCall;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

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

    /**
     * @return mixed[][]
     */
    public function provideData(): array
    {
        return [
            # on self call
            [__DIR__ . '/MethodCallSource/OnSelfCall.php.inc', 0, [
                'SomeClass',
                'Nette\Config\Configurator',
                'Nette\Object',
            ]],
            [__DIR__ . '/MethodCallSource/OnSelfCall.php.inc', 1, [
                'SomeClass',
                'Nette\Config\Configurator',
                'Nette\Object',
            ]],
            # on method call
            [__DIR__ . '/MethodCallSource/OnMethodCallCall.php.inc', 0, ['Nette\DI\Container']],
            # on variable call
            [__DIR__ . '/MethodCallSource/OnVariableCall.php.inc', 0, [
                'Nette\Config\Configurator',
                'Nette\Object',
            ]],
            # on property call
            [__DIR__ . '/MethodCallSource/OnPropertyCall.php.inc', 0, [
                'Nette\Config\Configurator',
                'Nette\Object',
            ]],
            # on magic class call
            [__DIR__ . '/MethodCallSource/OnMagicClassCall.php.inc', 0, [
                'Nette\Config\Configurator',
                'Nette\Object',
            ]],
        ];
    }
}
