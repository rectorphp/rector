<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

function someOtherFunctionWithNoEffect()
{
}

\Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture\someOtherFunctionWithNoEffect();

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

function someOtherFunctionWithNoEffect()
{
}

someOtherFunctionWithNoEffect();

?>
