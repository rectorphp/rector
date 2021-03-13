<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

class SameNamespacedClass
{
    /**
     * @return \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SharedShortName
     */
    public function run(): \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SharedShortName
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SharedShortName;
class SameNamespacedClass
{
    /**
     * @return SharedShortName
     */
    public function run(): SharedShortName
    {
    }
}

?>
