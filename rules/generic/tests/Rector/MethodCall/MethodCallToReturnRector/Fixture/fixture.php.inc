<?php

namespace Rector\Generic\Tests\Rector\MethodCall\MethodCallToReturnRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;

class SomeClass
{
    public function run(ReturnDeny $returnDeny)
    {
        $returnDeny->deny();
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\MethodCall\MethodCallToReturnRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;

class SomeClass
{
    public function run(ReturnDeny $returnDeny)
    {
        return $returnDeny->deny();
    }
}

?>
