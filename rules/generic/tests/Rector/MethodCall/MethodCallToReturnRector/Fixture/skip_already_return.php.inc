<?php

namespace Rector\Generic\Tests\Rector\MethodCall\MethodCallToReturnRector\Fixture;

use Rector\Generic\Tests\Rector\MethodCall\MethodCallToReturnRector\Source\ReturnDeny;

class SkipAlreadyReturn
{
    public function run(ReturnDeny $returnDeny)
    {
        return $returnDeny->deny();
    }

    public function whatIf(ReturnDeny $returnDeny)
    {
        if (true) {
            return $returnDeny->deny();
        }
    }
}
