<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector\Fixture;

final class SomeUnionType
{
    private int|string $age = 100;

    public function getAge()
    {
        return $this->age;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector\Fixture;

final class SomeUnionType
{
    private int|string $age = 100;

    public function getAge(): int|string
    {
        return $this->age;
    }
}

?>
