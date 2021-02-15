<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class MultipleParams
{
    private int $age;

    private string $nick;

    public function setValues($age, $name)
    {
        $this->age = $age;
        $this->nick = $name;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class MultipleParams
{
    private int $age;

    private string $nick;

    public function setValues(int $age, string $name)
    {
        $this->age = $age;
        $this->nick = $name;
    }
}

?>
