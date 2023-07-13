<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\MakeEntityDateTimePropertyDateTimeInterfaceRector\Fixture;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'x_y_z')]
class NullableTypedPropertyDateTime
{
    /**
     * @var DateTime|null
     */
    #[ORM\Column(name: 'canceledAt', type: 'datetime', nullable: true)]
    private ?DateTimeInterface $canceledat = null;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\MakeEntityDateTimePropertyDateTimeInterfaceRector\Fixture;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'x_y_z')]
class NullableTypedPropertyDateTime
{
    /**
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(name: 'canceledAt', type: 'datetime', nullable: true)]
    private ?DateTimeInterface $canceledat = null;
}

?>
