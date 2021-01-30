<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\FixtureAutoImport;

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

namespace Rector\CodingStyle\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\FixtureAutoImport;

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
