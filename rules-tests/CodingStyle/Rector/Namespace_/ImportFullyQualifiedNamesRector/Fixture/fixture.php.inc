<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

class SomeClass
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
class SomeClass
{
    public function create()
    {
          return new AnotherClass;
    }
}

?>
