<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

final class ExitSome
{
    public function run()
    {
        echo 100;
        exit;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

final class ExitSome
{
    public function run(): never
    {
        echo 100;
        exit;
    }
}

?>
