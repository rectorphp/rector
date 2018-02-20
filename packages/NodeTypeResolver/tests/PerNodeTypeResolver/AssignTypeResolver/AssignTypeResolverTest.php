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
            [__DIR__ . '/Source/MethodCall.php.inc', 0, ['Nette\Config\Configurator', 'Nette\Object']],
            [__DIR__ . '/Source/MethodCall.php.inc', 2, ['Nette\Config\Configurator', 'Nette\Object']],
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
            # method call
            [__DIR__ . '/Source/MethodCall.php.inc', 1, ['Nette\DI\Container']],
        ];
    }

    public function testMethodCallOnClassConstant(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/ClassConstant.php.inc', Variable::class);

        $this->assertSame(
            ['Nette\Config\Configurator', 'Nette\Object'],
            $this->nodeTypeResolver->resolve($variableNodes[0])
        );

        $this->assertSame(
            ['Nette\Config\Configurator', 'Nette\Object'],
            $this->nodeTypeResolver->resolve($variableNodes[2])
        );
    }

    public function testMethodCallOnPropertyFetch(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/PropertyFetch.php.inc', Variable::class);

        $this->assertSame(
            ['Nette\Config\Configurator', 'Nette\Object'],
            $this->nodeTypeResolver->resolve($variableNodes[0])
        );

        $this->assertSame(
            ['Nette\Config\Configurator', 'Nette\Object'],
            $this->nodeTypeResolver->resolve($variableNodes[2])
        );
    }
}
