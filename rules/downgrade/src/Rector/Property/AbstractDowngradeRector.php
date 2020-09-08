<?php

declare(strict_types=1);

namespace Rector\Downgrade\Rector\Property;

use Rector\Downgrade\Contract\Rector\DowngradeRectorInterface;

abstract class AbstractDowngradeRector extends AbstractMaybeAddDocBlockRector implements DowngradeRectorInterface
{
}
