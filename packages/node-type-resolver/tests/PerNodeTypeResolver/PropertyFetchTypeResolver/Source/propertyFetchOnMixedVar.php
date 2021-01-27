<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\Source;

function propertyFetchOnMixedVar($props): void
{
    $props->number->xxx();
    $props->abc->xxx();
    $props->array->xxx();
}
