<?php

declare(strict_types=1);

namespace Rector\Caching\Contract\Rector;

/**
 * Rectors implementing this interface require to run with --clear-cache, so full application is analysed.
 * Such rules can be remove unused public method, remove unused class etc. They need full app to decide correctly.
 */
interface ZeroCacheRectorInterface
{
}
