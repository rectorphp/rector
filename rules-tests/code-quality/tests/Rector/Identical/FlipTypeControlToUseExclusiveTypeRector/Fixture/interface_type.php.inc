<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use DateTimeInterface;

class InterfaceType
{
    public function run()
    {
        /** @var DateTimeInterface|null $dateTimeInterface */
        $dateTimeInterface = $this->getdateTimeInterface();
        if ($dateTimeInterface === null) {
            return;
        }

        /** @var null|DateTimeInterface $dateTimeInterface */
        $dateTimeInterface = $this->getdateTimeInterface();
        if ($dateTimeInterface === null) {
            return;
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\Fixture;

use DateTimeInterface;

class InterfaceType
{
    public function run()
    {
        $dateTimeInterface = $this->getdateTimeInterface();
        if (!$dateTimeInterface instanceof \DateTimeInterface) {
            return;
        }

        $dateTimeInterface = $this->getdateTimeInterface();
        if (!$dateTimeInterface instanceof \DateTimeInterface) {
            return;
        }
    }
}

?>
