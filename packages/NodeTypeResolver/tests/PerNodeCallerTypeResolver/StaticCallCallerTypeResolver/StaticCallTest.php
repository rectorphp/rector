<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeCallerTypeResolver\StaticCallCallerTypeResolver;

use PhpParser\Node\Expr\StaticCall;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\PerNodeCallerTypeResolver\AbstractNodeCallerTypeResolverTest;

final class StaticCallTest extends AbstractNodeCallerTypeResolverTest
{
    public function testOnParentStaticCall(): void
    {
        $methodCallNodes = $this->getNodesForFileOfType(
            __DIR__ . '/StaticCallSource/OnParentStaticCall.php.inc',
            StaticCall::class
        );

        $this->doTestAttributeEquals($methodCallNodes[0], Attribute::CALLER_TYPES, ['Nette\Config\Configurator']);
    }
}
