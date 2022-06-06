<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Logging;

use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
final class CurrentRectorProvider
{
    /**
     * @var \Rector\Core\Contract\Rector\RectorInterface|null
     */
    private $currentRector;
    public function changeCurrentRector(RectorInterface $rector) : void
    {
        $this->currentRector = $rector;
    }
    public function getCurrentRector() : ?RectorInterface
    {
        return $this->currentRector;
    }
}
