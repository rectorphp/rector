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
            $variableNodes[0]->getAttribute(Attribute::TYPES)
        );

        $this->assertSame(
            ['Nette\Config\Configurator', 'Nette\Object'],
            $variableNodes[2]->getAttribute(Attribute::TYPES)
        );
    }

    public function testNewTwo(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/New.php.inc', Variable::class);

        $this->assertSame(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            $variableNodes[0]->getAttribute(Attribute::TYPES)[0]
        );

        $this->assertSame(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            $variableNodes[1]->getAttribute(Attribute::TYPES)[0]
        );
    }

    public function testMethodCall(): void
    {
        $variableNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/MethodCall.php.inc', Variable::class);

        $this->assertSame(
            ['Nette\DI\Container'],
            $variableNodes[1]->getAttribute(Attribute::TYPES)
        );
    }
}
