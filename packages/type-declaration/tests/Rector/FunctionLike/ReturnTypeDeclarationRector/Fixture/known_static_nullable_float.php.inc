<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class KnownStaticNullableFloat
{
    public function getFloating()
    {
        if ((bool) rand(0, 10)) {
            return 5.2;
        }

        $value = 5.3;
        return $value;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class KnownStaticNullableFloat
{
    public function getFloating(): float
    {
        if ((bool) rand(0, 10)) {
            return 5.2;
        }

        $value = 5.3;
        return $value;
    }
}

?>
