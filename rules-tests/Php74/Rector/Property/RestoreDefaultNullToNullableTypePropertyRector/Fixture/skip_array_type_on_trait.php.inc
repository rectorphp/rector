<?php

namespace Rector\Tests\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

trait SkipArrayTypeOnTrait
{
    /** @var stdClass[] */
    private array $targets;

    public function withTargets(stdClass ...$targets): self
    {
        $this->targets = $targets;

        return $this;
    }
}