<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FunctionLike;

use Rector\DowngradePhp72\Contract\Rector\DowngradeRectorInterface;

abstract class AbstractDowngradeRector extends AbstractMaybeAddDocBlockRector implements DowngradeRectorInterface
{
}
