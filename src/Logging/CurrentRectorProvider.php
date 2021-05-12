<?php

declare (strict_types=1);
namespace Rector\Core\Logging;

use Rector\Core\Contract\Rector\RectorInterface;
final class CurrentRectorProvider
{
    /**
     * @var \Rector\Core\Contract\Rector\RectorInterface|null
     */
    private $currentRector;
    public function changeCurrentRector(\Rector\Core\Contract\Rector\RectorInterface $rector) : void
    {
        $this->currentRector = $rector;
    }
    public function getCurrentRector() : ?\Rector\Core\Contract\Rector\RectorInterface
    {
        return $this->currentRector;
    }
}
