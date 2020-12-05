<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

class FixtureVariableUsageParam {
    /**
     * @param mixed $mixed
     */
    public function methodWithDateTime($mixed, \DateTime $dateTime)
    {
        var_dump($dateTime->modify('+1 day'));

        return $dateTime;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

class FixtureVariableUsageParam {
    /**
     * @param mixed $mixed
     * @param \DateTime|\DateTimeImmutable $dateTime
     */
    public function methodWithDateTime($mixed, \DateTimeInterface $dateTime)
    {
        var_dump($dateTime = $dateTime->modify('+1 day'));

        return $dateTime;
    }
}

?>
