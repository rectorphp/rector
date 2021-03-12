<?php

namespace Rector\Tests\Transform\Rector\Expression\MethodCallToReturnRector\Fixture;

use Rector\Tests\Transform\Rector\Expression\MethodCallToReturnRector\Source\ReturnDeny;

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

namespace Rector\Tests\Transform\Rector\Expression\MethodCallToReturnRector\Fixture;

use Rector\Tests\Transform\Rector\Expression\MethodCallToReturnRector\Source\ReturnDeny;

class SomeClass
{
    public function run(ReturnDeny $returnDeny)
    {
        return $returnDeny->deny();
    }
}

?>
