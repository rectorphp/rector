<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\Row;

final class AssignOfFluent
{
    public function run(Row $row)
    {
        $cell = $row->addCell()->setName(2);
        return $cell;
    }
}

?>
-----
<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\Row;

final class AssignOfFluent
{
    public function run(Row $row)
    {
        $cell = $row->addCell();
        $cell->setName(2);
        return $cell;
    }
}

?>
