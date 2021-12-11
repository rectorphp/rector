<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

final class InnerClosure
{
    public function __construct()
    {
        $a = function () {
            return new \stdClass;
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

final class InnerClosure
{
    public function __construct()
    {
        $a = function (): \stdClass {
            return new \stdClass;
        };
    }
}

?>
