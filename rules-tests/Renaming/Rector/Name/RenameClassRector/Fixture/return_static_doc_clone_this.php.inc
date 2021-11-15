<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

class ReturnStaticDocCloneThis extends \DateTime
{
    /**
     * @return static
     */
    public function run(\DateTime $dateTime)
    {
        $obj = clone $this;
        return $obj;
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

class ReturnStaticDocCloneThis extends \DateTime
{
    /**
     * @return static
     */
    public function run(\DateTimeInterface $dateTime)
    {
        $obj = clone $this;
        return $obj;
    }
}

?>
