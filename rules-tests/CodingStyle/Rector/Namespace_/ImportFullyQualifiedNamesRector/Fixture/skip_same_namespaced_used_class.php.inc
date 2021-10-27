<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

class SkipSameNamespacedUsedClass
{
    /**
     * @return \Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SharedShortName
     */
    public function run(): SharedShortName
    {
    }
}
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SharedShortName;
class SkipSameNamespacedUsedClass
{
    /**
     * @return SharedShortName
     */
    public function run(): SharedShortName
    {
    }
}
