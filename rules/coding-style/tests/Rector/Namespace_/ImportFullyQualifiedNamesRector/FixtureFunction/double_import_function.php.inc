<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureFunction;

class DoubleImportFunction
{
    public function create()
    {
        return \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\substr(1, 2);
    }

    public function emulate()
    {
        return \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\substr(4, 5);
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureFunction;

use function Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Function_\substr;
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
