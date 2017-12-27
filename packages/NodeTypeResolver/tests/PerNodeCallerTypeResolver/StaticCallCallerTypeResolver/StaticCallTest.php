<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeCallerTypeResolver\StaticCallCallerTypeResolver;

use PhpParser\Node\Expr\StaticCall;
use Rector\NodeTypeResolver\Tests\PerNodeCallerTypeResolver\AbstractNodeCallerTypeResolverTest;

final class StaticCallTest extends AbstractNodeCallerTypeResolverTest
{
    public function testOnParentStaticCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/StaticCallSource/OnParentStaticCall.php.inc',
            StaticCall::class
        );

        $this->assertSame(
            ['Nette\Config\Configurator'],
            $this->nodeCallerTypeResolver->resolve($methodCallNodes[0])
        );
    }
}
