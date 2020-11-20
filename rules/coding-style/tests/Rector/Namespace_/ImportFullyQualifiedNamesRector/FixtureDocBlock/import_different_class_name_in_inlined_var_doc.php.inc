<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureDocBlock;

class ImportDifferentClassNameInInlinedVarDoc
{
    public function run($anotherVariable)
    {
        /** @var \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SkipAlreadyClassNameInInlinedVarDoc $typedVariable */
        $typedVariable = $anotherVariable;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureDocBlock;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SkipAlreadyClassNameInInlinedVarDoc;
class ImportDifferentClassNameInInlinedVarDoc
{
    public function run($anotherVariable)
    {
        /** @var SkipAlreadyClassNameInInlinedVarDoc $typedVariable */
        $typedVariable = $anotherVariable;
    }
}

?>
