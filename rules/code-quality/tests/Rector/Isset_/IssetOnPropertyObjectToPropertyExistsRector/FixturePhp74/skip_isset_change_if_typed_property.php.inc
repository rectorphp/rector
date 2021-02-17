<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\FixturePhp74;

final class SkipIssetChangeIfTypedProperty
{
    private string $cachedBaseRoutePattern;

    public function getBaseRoutePattern(): string
    {
        if (isset($this->cachedBaseRoutePattern)) {
            return $this->cachedBaseRoutePattern;
        }
    }
}
