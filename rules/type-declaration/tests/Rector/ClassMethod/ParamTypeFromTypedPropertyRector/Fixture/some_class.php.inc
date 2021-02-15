<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromTypedPropertyRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromTypedPropertyRector\Fixture;

final class SomeClass
{
    private int $age;

    public function setAge(int $age)
    {
        $this->age = $age;
    }
}

?>
