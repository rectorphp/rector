<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ClassMethodTypeResolver;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Tests\AbstractNodeTypeResolverTest;

final class Test extends AbstractNodeTypeResolverTest
{
    public function test(): void
    {
        $classMethodNodes = $this->getNodesForFileOfType(
            __DIR__ . '/Source/ClassMethodWithParent.php.inc',
            ClassMethod::class
        );

        $this->doTestAttributeEquals($classMethodNodes[0], Attribute::TYPES, [
            'SomeNamespace\SomeClass',
            'Doctrine\ORM\Persisters\Entity\AbstractEntityInheritancePersister',
        ]);
    }
}
