<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallCallerTypeResolver;

use PhpParser\Node\Expr\MethodCall;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

final class MethodCallTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $position, array $expectedTypes): void
    {
        $methodCallNodes = $this->getNodesForFileOfType($file, MethodCall::class);
        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($methodCallNodes[$position]));
    }

    /**
     * @return string[][]|int[][]|string[][][]
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
        ];
    }
}
