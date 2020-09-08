<?php

declare(strict_types=1);

namespace Rector\Downgrade\Contract\Rector;

use PhpParser\Node\Stmt\Property;

interface DowngradeTypedPropertyRectorInterface
{
    public function shouldSkip(Property $property): bool;
}
