<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ExternalList;

final class ForeachLiteral
{
    public function run(): array
    {
        $map = [];
        foreach (ExternalList::VALUES as $externalValue) {
            $map[$externalValue] = 100;
        }
        return $map;
    }
}
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\ExternalList;

final class ForeachLiteral
{
    /**
     * @return array<string, int>
     */
    public function run(): array
    {
        $map = [];
        foreach (ExternalList::VALUES as $externalValue) {
            $map[$externalValue] = 100;
        }
        return $map;
    }
}
