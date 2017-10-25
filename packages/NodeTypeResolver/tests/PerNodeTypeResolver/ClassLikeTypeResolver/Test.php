<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassLikeTypeResolver;

use PhpParser\Node\Expr\Variable;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class Test extends AbstractNodeTypeResolverTest
{
    public function test(): void
    {
        $file = __DIR__ . '/Source/ClassWithParent.php.inc';
        $variableNodes = $this->getNodesForFileOfType($file, Variable::class);

        $this->assertSame(
            'SomeNamespace\SomeClass_SomeNamespace\SomeInterface',
            $variableNodes[0]->getAttribute(Attribute::TYPE)
        );
    }
}
