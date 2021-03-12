<?php

declare(strict_types=1);

namespace Rector\Tests\Defluent\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source;

use Nette\Utils\DateTime;

final class SetGetDateTime
{
    /**
     * @var DateTime|null
     */
    private $dateMin = null;

    public function setDateMin(?DateTime $dateTime = null)
    {
        $this->dateMin = $dateTime;
    }

    public function getDateMin(): ?DateTime
    {
        return $this->dateMin;
    }
}
