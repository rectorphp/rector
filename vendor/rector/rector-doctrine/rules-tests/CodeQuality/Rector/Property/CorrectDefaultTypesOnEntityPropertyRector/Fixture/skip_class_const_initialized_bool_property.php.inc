<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

define("DEFAULT_CONST") = true;

final class SkipConstInitializedBoolProperty
{
    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $isActive = DEFAULT_CONST;
}
