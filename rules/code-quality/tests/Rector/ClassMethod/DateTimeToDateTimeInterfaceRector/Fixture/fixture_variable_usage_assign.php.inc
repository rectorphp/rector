<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

class FixtureVariableUsageAssign {
    /**
     * @param mixed $mixed
     */
    public function methodWithDateTime($mixed, \DateTime $dateTime)
    {
        $dateTime = $dateTime->modify('+1 day');

        return $dateTime;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

class FixtureVariableUsageAssign {
    /**
     * @param mixed $mixed
     * @param \DateTime|\DateTimeImmutable $dateTime
     */
    public function methodWithDateTime($mixed, \DateTimeInterface $dateTime)
    {
        $dateTime = $dateTime->modify('+1 day');

        return $dateTime;
    }
}

?>
