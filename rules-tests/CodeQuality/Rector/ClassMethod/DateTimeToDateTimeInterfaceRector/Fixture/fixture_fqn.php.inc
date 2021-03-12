<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

class FixtureFqn {
    public function methodWithDateTime(\DateTime $dateTime)
    {
        return true;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\Fixture;

class FixtureFqn {
    /**
     * @param \DateTime|\DateTimeImmutable $dateTime
     */
    public function methodWithDateTime(\DateTimeInterface $dateTime)
    {
        return true;
    }
}

?>
