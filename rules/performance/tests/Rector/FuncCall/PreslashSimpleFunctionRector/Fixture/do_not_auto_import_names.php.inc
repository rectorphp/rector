<?php

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

class DoNotAutoImportNames
{
    public function shorten($value)
    {
        return trim($value);
    }
}

?>
-----
<?php

namespace Rector\Performance\Tests\Rector\FuncCall\PreslashSimpleFunctionRector\Fixture;

class DoNotAutoImportNames
{
    public function shorten($value)
    {
        return \trim($value);
    }
}

?>
