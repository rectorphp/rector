<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

class ImportParamDoc
{
    /**
     * @param \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\NormalParamClass $alreadyClassName
     * @return \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\NormalParamClass
     */
    public function create(\Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\NormalParamClass $alreadyClassName)
    {
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\NormalParamClass;
class ImportParamDoc
{
    /**
     * @param NormalParamClass $alreadyClassName
     * @return NormalParamClass
     */
    public function create(NormalParamClass $alreadyClassName)
    {
    }
}

?>
