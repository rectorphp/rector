<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source;

use Cassandra\Date;
use Nette\Utils\DateTime;

final class SetGetDateTime
{
    private ?DateTime $dateMin = null;

    public function setDateMin(?DateTime $dateTime = null)
    {
        $this->dateMin = $dateTime;
    }

    public function getDateMin(): ?DateTime
    {
        return $this->dateMin;
    }
}
