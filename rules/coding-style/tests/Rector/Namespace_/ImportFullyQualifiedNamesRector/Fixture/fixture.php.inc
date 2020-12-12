<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

class Fixture
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
class Fixture
{
    public function create()
    {
          return new AnotherClass;
    }
}

?>
