<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

use Exception;

final class DoNotChangeParentClosure
{
    public function run()
    {
        function ()
        {
            throw new Exception('test');
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

use Exception;

final class DoNotChangeParentClosure
{
    public function run()
    {
        function (): never
        {
            throw new Exception('test');
        };
    }
}

?>
