<?php

namespace Rector\Core\Tests\Rector\MethodCall\MethodCallToReturnRector\Fixture;

use Rector\Core\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;

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

namespace Rector\Core\Tests\Rector\MethodCall\MethodCallToReturnRector\Fixture;

use Rector\Core\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;

class SomeClass
{
    public function run(ReturnDeny $returnDeny)
    {
        return $returnDeny->deny();
    }
}

?>
