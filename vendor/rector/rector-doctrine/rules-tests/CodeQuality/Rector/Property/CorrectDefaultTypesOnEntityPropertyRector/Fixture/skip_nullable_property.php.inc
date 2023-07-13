<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

final class SkipNullableProperty
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $entityId = null;
}
