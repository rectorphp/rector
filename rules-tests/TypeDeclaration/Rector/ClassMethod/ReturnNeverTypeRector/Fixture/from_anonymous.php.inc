<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

use Exception;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Source\MixedReturn;

new class implements MixedReturn {
    public function run()
    {
        throw new Exception('test');
    }
};

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Fixture;

use Exception;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\Source\MixedReturn;

new class implements MixedReturn {
    public function run(): never
    {
        throw new Exception('test');
    }
};

?>
