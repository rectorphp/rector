<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\NameTypeResolver;

use PhpParser\Node\Name;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class NameTypeResolverTest extends AbstractNodeTypeResolverTest
{
    public function testNew(): void
    {
        $nameNodes = $this->getNodesForFileOfType(__DIR__ . '/Source/ParentCall.php.inc', Name::class);

        $this->assertSame(['Nette\Config\Configurator'], $nameNodes[2]->getAttribute(Attribute::TYPES));
    }
}
