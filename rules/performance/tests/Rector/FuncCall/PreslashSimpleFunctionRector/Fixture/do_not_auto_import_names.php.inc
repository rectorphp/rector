<?php

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

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

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

use stdClass;
class DoNotAutoImportNames
{
    public function shorten($value)
    {
        new stdClass;
        return trim($value);
    }
}

?>
