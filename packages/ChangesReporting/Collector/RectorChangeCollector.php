<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Collector;

use PhpParser\Node;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
final class RectorChangeCollector
{
    /**
     * @readonly
     * @var \Rector\Core\Logging\CurrentRectorProvider
     */
    private $currentRectorProvider;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    public function __construct(CurrentRectorProvider $currentRectorProvider, CurrentFileProvider $currentFileProvider)
    {
        $this->currentRectorProvider = $currentRectorProvider;
        $this->currentFileProvider = $currentFileProvider;
    }
    /**
     * @internal Use file-> method instead
     */
    public function notifyNodeFileInfo(Node $node) : void
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            // this file was changed before and this is a sub-new node
            // array Traverse to all new nodes would have to be used, but it's not worth the performance
            return;
        }
        $currentRector = $this->currentRectorProvider->getCurrentRector();
        if (!$currentRector instanceof RectorInterface) {
            return;
        }
        $rectorWithLineChange = new RectorWithLineChange(\get_class($currentRector), $node->getLine());
        $file->addRectorClassWithLine($rectorWithLineChange);
    }
}
