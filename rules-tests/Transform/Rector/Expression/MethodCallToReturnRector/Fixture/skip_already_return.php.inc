<?php

namespace Rector\Tests\Transform\Rector\Expression\MethodCallToReturnRector\Fixture;

use Rector\Tests\Transform\Rector\Expression\MethodCallToReturnRector\Source\ReturnDeny;

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
