<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

class FixtureVariableUsage {
    /**
     * @param mixed $mixed
     */
    public function methodWithDateTime($mixed, \DateTime $dateTime)
    {
        $dateTime->modify('+1 day');

        return $dateTime;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

class FixtureVariableUsage {
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
