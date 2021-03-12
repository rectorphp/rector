<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector\FixtureAutoImport;

class DoNotAutoImportNames
{
    public function shorten($value)
    {
        new \stdClass;
        return trim($value);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\PreslashSimpleFunctionRector\FixtureAutoImport;

use stdClass;
class DoNotAutoImportNames
{
    public function shorten($value)
    {
        new stdClass;
        return \trim($value);
    }
}

?>
