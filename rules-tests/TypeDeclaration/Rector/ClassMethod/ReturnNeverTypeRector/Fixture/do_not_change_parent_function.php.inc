<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

use Exception;

final class DoNotChangeParentFunction
{
    public function run()
    {
        function run()
        {
            throw new Exception('test');
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

use Exception;

final class DoNotChangeParentFunction
{
    public function run()
    {
        function run(): never
        {
            throw new Exception('test');
        }
    }
}

?>
