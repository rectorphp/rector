<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class EvenConstructor
{
    private int $age;

    public function __construct(int $age)
    {
        $this->age = $age;
    }
}

?>
