<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

class ImportFunction
{
    public function run()
    {
        return \Safe\count([1]);
    }
}

function someFunctionWithNoEffect()
{
}

\Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture\someFunctionWithNoEffect();

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use function Safe\count;
class ImportFunction
{
    public function run()
    {
        return count([1]);
    }
}

function someFunctionWithNoEffect()
{
}

someFunctionWithNoEffect();

?>
