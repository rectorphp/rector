<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

function noClass()
{
    return new \SomeAnother\AnotherClass;
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use SomeAnother\AnotherClass;
function noClass()
{
    return new AnotherClass;
}

?>
