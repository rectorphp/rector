<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use DateTime;
use Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Source\SomeValueObject;

class PassClassConstFetchArg
{
    private DateTime $dateTime;

    public function __construct(
        ?DateTime $dateTime = null
    ) {
        $this->dateTime = $dateTime ?? new DateTime(SomeValueObject::NOW);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Fixture;

use DateTime;
use Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\Source\SomeValueObject;

class PassClassConstFetchArg
{
    public function __construct(private DateTime $dateTime = new DateTime(SomeValueObject::NOW))
    {
    }
}

?>
