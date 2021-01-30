<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\FixtureAutoImport;

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

namespace Rector\CodingStyle\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\FixtureAutoImport;

use stdClass;
class AutoImportNames
{
    public function run()
    {
        return new stdClass();
    }
}

?>
