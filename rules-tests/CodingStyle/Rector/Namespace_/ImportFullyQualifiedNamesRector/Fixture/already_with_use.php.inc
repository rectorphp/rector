<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

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

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

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
