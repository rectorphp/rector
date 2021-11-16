<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use DateTime;
use DateTimeInterface;

class ArrayStatic extends \DateTime
{
    /**
     * @var static[]|null
     */
    private $dateTime;

    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = rand(0,1)
            ? [$dateTime]
            : null;
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use DateTime;
use DateTimeInterface;

class ArrayStatic extends \DateTime
{
    /**
     * @var static[]|null
     */
    private $dateTime;

    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = rand(0,1)
            ? [$dateTime]
            : null;
    }
}

?>
