<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AssignTypeResolver;

use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\AssignTypeResolver
 */
final class AssignTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideTypeForNodesAndFilesData()
     * @param string[] $expectedTypes
     */
    public function test(string $file, int $nodePosition, array $expectedTypes): void
    {
        $variableNodes = $this->getNodesForFileOfType($file, Variable::class);

        $this->assertSame($expectedTypes, $this->nodeTypeResolver->resolve($variableNodes[$nodePosition]));
    }

    /**
     * @return mixed[][]
     */
    public function provideTypeForNodesAndFilesData(): array
    {
        return [
            # assign of "new <name>"
            [__DIR__ . '/Source/New.php.inc', 0, [
                'Symfony\Component\DependencyInjection\ContainerBuilder',
                'Symfony\Component\DependencyInjection\ResettableContainerInterface',
                'Symfony\Component\DependencyInjection\ContainerInterface',
                'Psr\Container\ContainerInterface',
                'Symfony\Component\DependencyInjection\TaggedContainerInterface',
                'Symfony\Component\DependencyInjection\Container',
            ]],
            [__DIR__ . '/Source/New.php.inc', 1, [
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
     * @dataProvider provideTypeForNodesAndFilesDataForPhp71()
     * @param string[] $expectedTypes
     */
    public function testPhp71(string $file, int $nodePosition, array $expectedTypes): void
    {
        if (PHP_VERSION >= '7.2.0') {
            $this->markTestSkipped('This test needs PHP 7.1 or lower.');
        }

        $this->test($file, $nodePosition, $expectedTypes);
    }

    /**
     * @return mixed[][]
     */
    public function provideTypeForNodesAndFilesDataForPhp71(): array
    {
        return [
            # assign of "new <name>"
            [__DIR__ . '/Source/MethodCall.php.inc', 0, ['Nette\Config\Configurator', 'Nette\Object']],
            [__DIR__ . '/Source/MethodCall.php.inc', 2, ['Nette\Config\Configurator', 'Nette\Object']],
            # method call
            [__DIR__ . '/Source/MethodCall.php.inc', 1, ['Nette\DI\Container']],
            # method call on class constant
            [__DIR__ . '/Source/ClassConstant.php.inc', 0, ['Nette\Config\Configurator', 'Nette\Object']],
            [__DIR__ . '/Source/ClassConstant.php.inc', 2, ['Nette\Config\Configurator', 'Nette\Object']],
            # method call on property fetch
            [__DIR__ . '/Source/PropertyFetch.php.inc', 0, ['Nette\Config\Configurator', 'Nette\Object']],
            [__DIR__ . '/Source/PropertyFetch.php.inc', 2, ['Nette\Config\Configurator', 'Nette\Object']],
        ];
    }
}
