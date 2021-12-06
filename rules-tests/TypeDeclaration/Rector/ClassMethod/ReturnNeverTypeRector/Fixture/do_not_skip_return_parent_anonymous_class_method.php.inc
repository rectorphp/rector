<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

final class DoNotSkipReturnParentAnonymousClassMethod
{
    public function run()
    {
        new class {
            public function run()
            {
                return;
            }
        };

        exit();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

final class DoNotSkipReturnParentAnonymousClassMethod
{
    public function run(): never
    {
        new class {
            public function run()
            {
                return;
            }
        };

        exit();
    }
}

?>
