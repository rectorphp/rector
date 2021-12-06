<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

final class DoNotSkipYieldParentAnonymousClassMethod
{
    public function run()
    {
        new class {
            public function run()
            {
                yield 1;
                exit();
            }
        };

        exit();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

final class DoNotSkipYieldParentAnonymousClassMethod
{
    public function run(): never
    {
        new class {
            public function run()
            {
                yield 1;
                exit();
            }
        };

        exit();
    }
}

?>
