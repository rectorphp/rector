<?php

declare(strict_types=1);

namespace Rector\Core\Guard;

use Rector\Core\Application\ActiveRectorsProvider;
use Rector\Core\Exception\NoRectorsLoadedException;

final class RectorGuard
{
    /**
     * @var ActiveRectorsProvider
     */
    private $activeRectorsProvider;

    public function __construct(ActiveRectorsProvider $activeRectorsProvider)
    {
        $this->activeRectorsProvider = $activeRectorsProvider;
    }

    public function ensureSomeRectorsAreRegistered(): void
    {
        if ($this->activeRectorsProvider->provide() !== []) {
            return;
        }

        // @todo @dx display nicer way instead of all red, as in https://github.com/symplify/symplify/blame/master/packages/easy-coding-standard/bin/ecs#L69-L83
        throw new NoRectorsLoadedException(sprintf(
            'We need some rectors to run:%s* register them in rector.php under "services():"%s* use "--set <set>"%s* or use "--config <file>.yaml"',
            PHP_EOL,
            PHP_EOL,
            PHP_EOL
        ));
    }
}
