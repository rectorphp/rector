<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

function someOtherFunctionWithNoEffect()
{
}

\Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture\someOtherFunctionWithNoEffect();

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

function someOtherFunctionWithNoEffect()
{
}

someOtherFunctionWithNoEffect();

?>
