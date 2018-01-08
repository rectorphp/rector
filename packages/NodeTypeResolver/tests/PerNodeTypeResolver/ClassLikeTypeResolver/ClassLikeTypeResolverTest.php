<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassLikeTypeResolver;

use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\AbstractNodeTypeResolverTest;

/**
 * @covers \Rector\NodeTypeResolver\PerNodeTypeResolver\ClassLikeTypeResolver
 */
final class ClassLikeTypeResolverTest extends AbstractNodeTypeResolverTest
{
    public function test(): void
    {
        $file = __DIR__ . '/Source/ClassWithParent.php.inc';
        $variableNodes = $this->getNodesForFileOfType($file, Variable::class);

        $this->assertSame(
            ['SomeNamespace\SomeClass', 'SomeNamespace\SomeInterface'],
            $this->nodeTypeResolver->resolve($variableNodes[0])
        );
    }
}
