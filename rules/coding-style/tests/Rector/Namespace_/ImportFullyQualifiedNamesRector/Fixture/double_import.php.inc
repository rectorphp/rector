<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Me\Properly;

class AlreadyWithDoubleImport
{
    public function create()
    {
          return new \SomeAnother\AnotherClass;
    }

    public function createTheSame()
    {
        return new \SomeAnother\AnotherClass;
    }

    public function emulate()
    {
        return new \SomeAnother\YetAnotherClass;
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use SomeAnother\AnotherClass;
use SomeAnother\YetAnotherClass;
use Me\Properly;

class AlreadyWithDoubleImport
{
    public function create()
    {
          return new AnotherClass;
    }

    public function createTheSame()
    {
        return new AnotherClass;
    }

    public function emulate()
    {
        return new YetAnotherClass;
    }
}

?>
