<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class NestedKeyType
{
    public function getValues(): array
    {
        return [
            'string' => 1000
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class NestedKeyType
{
    /**
     * @return array<string, int>
     */
    public function getValues(): array
    {
        return [
            'string' => 1000
        ];
    }
}

?>
