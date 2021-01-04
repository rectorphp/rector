<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Fixture;

use DateTime;
use DateTimeZone;
use Rector\Defluent\Tests\Rector\MethodCall\InArgChainFluentMethodCallToStandaloneMethodCallRectorTest\Source\SetGetDateTime;
use Nette\Utils\DateTime as NetteDateTime;

final class SkipSelfSelf
{
    public function run(SetGetDateTime $setGetDateTime, DateTimeZone $dateTimeZone)
    {
        if (! empty($setGetDateTime->getDateMin()) && $setGetDateTime->getDateMin() instanceof DateTime) {
            $setGetDateTime->setDateMin($setGetDateTime->getDateMin()->setTimezone($dateTimeZone));
        }
    }
}
