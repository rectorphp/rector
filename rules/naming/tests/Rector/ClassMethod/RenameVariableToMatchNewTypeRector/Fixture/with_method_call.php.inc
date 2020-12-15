<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Source\DreamSearch;

final class WithMethodCall
{
    public function run()
    {
        $search = $this->getDreamSearch();
        $search->reachIt();
    }

    private function getDreamSearch(): DreamSearch
    {
        return new DreamSearch();
    }
}
?>
-----
<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Source\DreamSearch;

final class WithMethodCall
{
    public function run()
    {
        $dreamSearch = $this->getDreamSearch();
        $dreamSearch->reachIt();
    }

    private function getDreamSearch(): DreamSearch
    {
        return new DreamSearch();
    }
}

?>
