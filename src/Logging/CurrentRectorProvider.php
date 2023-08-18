<?php

declare (strict_types=1);
namespace Rector\Core\Logging;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
final class CurrentRectorProvider
{
    /**
     * @var \Rector\Core\Contract\Rector\RectorInterface|\Rector\PostRector\Contract\Rector\PostRectorInterface|null
     */
    private $currentRector = null;
    /**
     * @param \Rector\Core\Contract\Rector\RectorInterface|\Rector\PostRector\Contract\Rector\PostRectorInterface $rector
     */
    public function changeCurrentRector($rector) : void
    {
        $this->currentRector = $rector;
    }
    /**
     * @return \Rector\Core\Contract\Rector\RectorInterface|\Rector\PostRector\Contract\Rector\PostRectorInterface|null
     */
    public function getCurrentRector()
    {
        return $this->currentRector;
    }
}
