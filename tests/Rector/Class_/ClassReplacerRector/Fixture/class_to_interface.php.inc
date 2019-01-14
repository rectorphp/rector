<?php

namespace Rector\Tests\Rector\Class_\ClassReplacerRector\Fixture;

class ClassToInterface
{
    public function run(\DateTime $dateTime): \DateTime
    {
        $oldClassWithTypo = new \DateTime();
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Class_\ClassReplacerRector\Fixture;

class ClassToInterface
{
    public function run(\DateTimeInterface $dateTime): \DateTimeInterface
    {
        $oldClassWithTypo = new \DateTime();
    }
}

?>
