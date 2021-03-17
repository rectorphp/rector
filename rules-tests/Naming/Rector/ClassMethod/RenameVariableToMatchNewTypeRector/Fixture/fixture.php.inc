<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Source\DreamSearch;

final class SomeClass
{
    public function run()
    {
        $search = new DreamSearch();
        $search->reachIt();
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Source\DreamSearch;

final class SomeClass
{
    public function run()
    {
        $dreamSearch = new DreamSearch();
        $dreamSearch->reachIt();
    }
}

?>
