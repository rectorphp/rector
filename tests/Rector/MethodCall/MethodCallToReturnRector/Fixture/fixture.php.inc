<?php

namespace Rector\Tests\Rector\MethodCall\MethodCallToReturnRector\Fixture;

use Rector\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;

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

namespace Rector\Tests\Rector\MethodCall\MethodCallToReturnRector\Fixture;

use Rector\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;

class SomeClass
{
    public function run(ReturnDeny $returnDeny)
    {
        return $returnDeny->deny();
    }
}

?>
