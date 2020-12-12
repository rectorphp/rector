<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Source\DreamSearch;

final class Fixture
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

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Source\DreamSearch;

final class Fixture
{
    public function run()
    {
        $dreamSearch = new DreamSearch();
        $dreamSearch->reachIt();
    }
}

?>
