<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class SomeClass
{
    private int $age;

    public function setAge($age)
    {
        $this->age = $age;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class SomeClass
{
    private int $age;

    public function setAge(int $age)
    {
        $this->age = $age;
    }
}

?>
