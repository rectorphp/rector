<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use DateTime;

class SkipDateTimeModifyCommand
{
    public function go()
    {
        $billingDate = $this->getBillingDate()->modify('00:00');
    }

    public function getBillingDate(): DateTime
    {
        return new DateTime('now');
    }
}
