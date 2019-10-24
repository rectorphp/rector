<?php

namespace Rector\MethodCall\Tests\Rector\MethodCall\MethodCallToReturnRector\Fixture;

use Rector\MethodCall\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;

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

namespace Rector\MethodCall\Tests\Rector\MethodCall\MethodCallToReturnRector\Fixture;

use Rector\MethodCall\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;

class SomeClass
{
    public function run(ReturnDeny $returnDeny)
    {
        return $returnDeny->deny();
    }
}

?>
