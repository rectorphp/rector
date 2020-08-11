<?php

namespace Rector\Generic\Tests\Rector\StaticCall\RemoveParentCallByParentRector\Fixture;

use Rector\Generic\Tests\Rector\StaticCall\RemoveParentCallByParentRector\Source\ParentClassToRemoveParentStaticCallBy;

final class SomeClass extends ParentClassToRemoveParentStaticCallBy
{
    public function run()
    {
        parent::run();
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\StaticCall\RemoveParentCallByParentRector\Fixture;

use Rector\Generic\Tests\Rector\StaticCall\RemoveParentCallByParentRector\Source\ParentClassToRemoveParentStaticCallBy;

final class SomeClass extends ParentClassToRemoveParentStaticCallBy
{
    public function run()
    {
    }
}

?>
