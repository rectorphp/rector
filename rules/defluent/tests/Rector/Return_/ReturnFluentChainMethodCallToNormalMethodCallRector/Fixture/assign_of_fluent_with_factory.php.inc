<?php

namespace Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\Cell;
use Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\Row;

final class AssignOfFluentWithFactory
{
    public function run(Row $row): Cell
    {
        return $row->addCell()->setName(2);
    }
}

?>
-----
<?php

namespace Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\Cell;
use Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\Row;

final class AssignOfFluentWithFactory
{
    public function run(Row $row): Cell
    {
        $cell = $row->addCell();
        $cell->setName(2);
        return $cell;
    }
}

?>
