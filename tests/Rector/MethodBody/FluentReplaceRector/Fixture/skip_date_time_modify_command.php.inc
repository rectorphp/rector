<?php

namespace Rector\Core\Tests\Rector\MethodBody\FluentReplaceRector;

use DateTime;
use Symfony\Component\Console\Command\Command;

class SkipDateTimeModifyCommand extends Command
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
