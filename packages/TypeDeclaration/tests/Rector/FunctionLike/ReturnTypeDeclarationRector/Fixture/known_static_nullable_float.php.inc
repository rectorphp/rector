<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class KnownStaticNullableFloat
{
    public function getFloating()
    {
        if (true) {
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
        if (true) {
            return 5.2;
        }

        $value = 5.3;
        return $value;
    }
}

?>
