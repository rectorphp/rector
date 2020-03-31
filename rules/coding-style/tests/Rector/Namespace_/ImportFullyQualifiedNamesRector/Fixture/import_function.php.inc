<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

class ImportFunction
{
    public function run()
    {
        return \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\count([1]);
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

use function Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\count;
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
