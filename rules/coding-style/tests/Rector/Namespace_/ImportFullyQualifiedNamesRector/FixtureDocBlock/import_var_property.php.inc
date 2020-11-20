<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureDocBlock;

class ImportVarProperty
{
    /**
     * @var \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SkipAlreadyClassNameInVarDoc $result
     */
    public $some;
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureDocBlock;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SkipAlreadyClassNameInVarDoc;
class ImportVarProperty
{
    /**
     * @var SkipAlreadyClassNameInVarDoc $result
     */
    public $some;
}

?>
