<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

class ImportParamDoc
{
    /**
     * @param \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\AlreadyClassName $alreadyClassName
     * @return \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\AlreadyClassName
     */
    public function create(\Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\AlreadyClassName $alreadyClassName)
    {
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\AlreadyClassName;
class ImportParamDoc
{
    /**
     * @param AlreadyClassName $alreadyClassName
     * @return AlreadyClassName
     */
    public function create(AlreadyClassName $alreadyClassName)
    {
    }
}

?>
