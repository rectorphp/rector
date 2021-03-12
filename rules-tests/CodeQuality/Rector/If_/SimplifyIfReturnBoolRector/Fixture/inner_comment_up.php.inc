<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

class InnerCommentUp
{
    public function run()
    {
        if (method_exists($parentClassName, $methodName)) {
            // validate type is conflicting
            // parent class method in external scope → it's not ok
            return true;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

class InnerCommentUp
{
    public function run()
    {
        // validate type is conflicting
        // parent class method in external scope → it's not ok
        return method_exists($parentClassName, $methodName);
    }
}

?>
