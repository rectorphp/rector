<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector\Fixture;

final class TwoReturnsUnionType
{
    private int $age = 100;

    private string $name = 'Elon';

    public function getValue()
    {
        if (mt_rand(0, 100)) {
            return $this->age;
        }

        return $this->name;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector\Fixture;

final class TwoReturnsUnionType
{
    private int $age = 100;

    private string $name = 'Elon';

    public function getValue(): int|string
    {
        if (mt_rand(0, 100)) {
            return $this->age;
        }

        return $this->name;
    }
}

?>
