<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector\FixtureAutoImport;

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

namespace Rector\Tests\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector\FixtureAutoImport;

use stdClass;
class AutoImportNames
{
    public function run()
    {
        return new stdClass();
    }
}

?>
