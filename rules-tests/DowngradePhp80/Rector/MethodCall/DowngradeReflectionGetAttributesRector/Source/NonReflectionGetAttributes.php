<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector\Source;

final class NonReflectionGetAttributes
{
    public function getAttributes(): array
    {
        return [];
    }
}
