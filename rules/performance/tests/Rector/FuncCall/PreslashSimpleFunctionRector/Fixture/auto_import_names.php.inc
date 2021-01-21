<?php

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

class AutoImportNames
{
    public function run()
    {
        return new \stdClass();
    }
}

?>
-----
<?php

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

use stdClass;
class AutoImportNames
{
    public function run()
    {
        return new stdClass();
    }
}

?>
