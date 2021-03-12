<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

class ClassToInterface extends \DateTime
{
    public function run(\DateTime $dateTime): \DateTime
    {
        $oldClassWithTypo = new \DateTime();
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

class ClassToInterface extends \DateTime
{
    public function run(\DateTimeInterface $dateTime): \DateTimeInterface
    {
        $oldClassWithTypo = new \DateTime();
    }
}

?>
