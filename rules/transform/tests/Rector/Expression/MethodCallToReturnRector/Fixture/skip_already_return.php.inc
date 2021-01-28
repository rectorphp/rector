<?php

namespace Rector\Transform\Tests\Rector\Expression\MethodCallToReturnRector\Fixture;

use Rector\Transform\Tests\Rector\Expression\MethodCallToReturnRector\Source\ReturnDeny;

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
