<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\RectorUseCasesFixture;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;

class VisibilitySorter
{
    private const POSITION = 'position';
    private const NAME = 'name';

    public function sortProperties(ClassLike $classLike): array
    {
        $properties = [];
        foreach ($classLike->stmts as $position => $propertyStmt) {
            if (! $propertyStmt instanceof Property) {
                continue;
            }

            /** @var string $propertyName */
            $propertyName = $this->nodeNameResolver->getName($propertyStmt);

            $properties[$propertyName][self::NAME] = $propertyName;
            $properties[$propertyName]['static'] = $propertyStmt->isStatic();
            $properties[$propertyName][self::POSITION] = $position;
        }

        return $properties;
    }
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\RectorUseCasesFixture;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;

class VisibilitySorter
{
    private const POSITION = 'position';
    private const NAME = 'name';

    /**
     * @return array<string, array<string, bool|int|string>>
     */
    public function sortProperties(ClassLike $classLike): array
    {
        $properties = [];
        foreach ($classLike->stmts as $position => $propertyStmt) {
            if (! $propertyStmt instanceof Property) {
                continue;
            }

            /** @var string $propertyName */
            $propertyName = $this->nodeNameResolver->getName($propertyStmt);

            $properties[$propertyName][self::NAME] = $propertyName;
            $properties[$propertyName]['static'] = $propertyStmt->isStatic();
            $properties[$propertyName][self::POSITION] = $position;
        }

        return $properties;
    }
}
?>
