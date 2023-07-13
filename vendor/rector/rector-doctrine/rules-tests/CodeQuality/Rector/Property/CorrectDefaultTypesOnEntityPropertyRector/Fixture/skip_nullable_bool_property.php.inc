<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

final class SkipNullableBoolProperty
{
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isActive = null;
}
