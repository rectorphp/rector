<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\RemoveEmptyTableAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'skip_not_empty')]
#[ORM\Entity]
class SkipNotEmpty
{
}
