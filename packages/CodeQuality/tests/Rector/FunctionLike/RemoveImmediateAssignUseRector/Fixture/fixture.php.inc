<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveImmediateAssignUseRector\Fixture;

class SomeClass
{
    public function run($remix)
    {
        $value = $remix->getThis();
        if ($value > 3) {
            return true;
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveImmediateAssignUseRector\Fixture;

class SomeClass
{
    public function run($remix)
    {
        if ($remix->getThis() > 3) {
            return true;
        }
    }
}

?>
