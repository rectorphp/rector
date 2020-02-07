<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Namespace_;

class ImportReturnDoc
{
    /**
     * @return Namespace_\ImportFullyQualifiedNamesRector\Source\NormalReturnClass
     */
    public function create()
    {
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\NormalReturnClass;
use Rector\CodingStyle\Tests\Rector\Namespace_;

class ImportReturnDoc
{
    /**
     * @return NormalReturnClass
     */
    public function create()
    {
    }
}

?>
