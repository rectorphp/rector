<?php declare(strict_types=1);

namespace Rector\RectorBuilder\Contract;

use Rector\Contract\Rector\RectorInterface;

interface RectorProviderInterface
{
    public function provide(): RectorInterface;
}
