<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AssignTypeResolver;

use PhpParser\Node\Expr\Variable;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class AssignTypeResolverTest extends AbstractNodeTypeResolverTest
{
    public function testNew(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/MethodCall.php.inc', Variable::class);

        $this->assertSame(
            ['Nette\Config\Configurator', 'Nette\Object'],
            $this->nodeTypeResolver->resolve($variableNodes[0])
        );

        $this->assertSame(
            ['Nette\Config\Configurator', 'Nette\Object'],
            $this->nodeTypeResolver->resolve($variableNodes[2])
        );
    }

    public function testNewTwo(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/New.php.inc', Variable::class);

        $this->assertSame(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            $this->nodeTypeResolver->resolve($variableNodes[0])[0]
        );

        $this->assertSame(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            $this->nodeTypeResolver->resolve($variableNodes[1])[0]
        );
    }

    public function testMethodCall(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/MethodCall.php.inc', Variable::class);

        $this->assertSame(
            ['Nette\DI\Container'],
            $this->nodeTypeResolver->resolve($variableNodes[1])
        );
    }
}
