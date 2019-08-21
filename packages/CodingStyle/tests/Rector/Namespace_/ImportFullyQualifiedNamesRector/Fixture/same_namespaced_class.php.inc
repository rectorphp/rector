<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

class SameNamespacedClass
{
    /**
     * @return \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SharedShortName
     */
    public function run(): \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SharedShortName
    {
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SharedShortName;
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
