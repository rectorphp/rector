<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

class ImportFunction
{
    public function run()
    {
        return \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\count([1]);
    }
}

function someFunctionWithNoEffect()
{
}

\Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture\someFunctionWithNoEffect();

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use function Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\count;
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
