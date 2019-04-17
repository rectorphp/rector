<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Me\Properly;

class AlreadyWithUse
{
    public function create()
    {
          return new \SomeAnother\AnotherClass;
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use SomeAnother\AnotherClass;
use Me\Properly;

class AlreadyWithUse
{
    public function create()
    {
          return new AnotherClass;
    }
}

?>
