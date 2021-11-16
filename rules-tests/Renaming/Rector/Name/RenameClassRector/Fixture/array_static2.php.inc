<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use DateTime;
use DateTimeInterface;

class ArrayStatic2 extends \DateTime
{
    /**
     * @var static[]
     */
    private $dateTime;

    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = [$dateTime];
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use DateTime;
use DateTimeInterface;

class ArrayStatic2 extends \DateTime
{
    /**
     * @var static[]
     */
    private $dateTime;

    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = [$dateTime];
    }
}

?>
