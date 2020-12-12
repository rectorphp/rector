<?php

namespace Rector\Generic\Tests\Rector\Expression\MethodCallToReturnRector\Fixture;

use Rector\Generic\Tests\Rector\Expression\MethodCallToReturnRector\Source\ReturnDeny;

class Fixture
{
    public function run(ReturnDeny $returnDeny)
    {
        $returnDeny->deny();
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\Expression\MethodCallToReturnRector\Fixture;

use Rector\Generic\Tests\Rector\Expression\MethodCallToReturnRector\Source\ReturnDeny;

class Fixture
{
    public function run(ReturnDeny $returnDeny)
    {
        return $returnDeny->deny();
    }
}

?>
