<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\RectorUseCasesFixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\Type;

class NestedArrayOfIndexedObjects
{
    private function resolveFetchedLocalPropertyNamesToTypes(Class_ $class): array
    {
        $fetchedLocalPropertyNameToTypes = [];

        foreach ($class->stmts as $node) {
            $propertyName = $this->getName($node->name);

            $propertyFetchType = $this->resolvePropertyFetchType($node);

            $fetchedLocalPropertyNameToTypes[$propertyName][] = $propertyFetchType;
        }

        return $fetchedLocalPropertyNameToTypes;
    }

    private function resolvePropertyFetchType(Node $node): Type
    {
    }

    private function getName($name): string
    {
    }
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\RectorUseCasesFixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\Type;

class NestedArrayOfIndexedObjects
{
    /**
     * @return array<string, \PHPStan\Type\Type[]>
     */
    private function resolveFetchedLocalPropertyNamesToTypes(Class_ $class): array
    {
        $fetchedLocalPropertyNameToTypes = [];

        foreach ($class->stmts as $node) {
            $propertyName = $this->getName($node->name);

            $propertyFetchType = $this->resolvePropertyFetchType($node);

            $fetchedLocalPropertyNameToTypes[$propertyName][] = $propertyFetchType;
        }

        return $fetchedLocalPropertyNameToTypes;
    }

    private function resolvePropertyFetchType(Node $node): Type
    {
    }

    private function getName($name): string
    {
    }
}
?>
