<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

final class SkipConstInitializedBoolProperty
{

    public const DEFAULT_CONST = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $isActive = self::DEFAULT_CONST;
}
