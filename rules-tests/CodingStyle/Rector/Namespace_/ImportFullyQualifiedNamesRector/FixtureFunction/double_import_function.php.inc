<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureFunction;

class DoubleImportFunction
{
    public function create()
    {
        return \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\substr(1, 2);
    }

    public function emulate()
    {
        return \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\substr(4, 5);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureFunction;

use function Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\substr;
class DoubleImportFunction
{
    public function create()
    {
        return substr(1, 2);
    }

    public function emulate()
    {
        return substr(4, 5);
    }
}

?>
