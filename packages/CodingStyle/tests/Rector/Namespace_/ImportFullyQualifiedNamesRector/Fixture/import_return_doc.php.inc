<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Namespace_;

class ImportReturnDoc
{
    /**
     * @return Namespace_\ImportFullyQualifiedNamesRector\Source\AlreadyClassName
     */
    public function create()
    {
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\AlreadyClassName;
use Rector\CodingStyle\Tests\Rector\Namespace_;

class ImportReturnDoc
{
    /**
     * @return AlreadyClassName
     */
    public function create()
    {
    }
}

?>
