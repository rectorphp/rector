<?php

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class EvenConstructor
{
    private int $age;

    public function __construct($age)
    {
        $this->age = $age;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class EvenConstructor
{
    private int $age;

    public function __construct(int $age)
    {
        $this->age = $age;
    }
}

?>
