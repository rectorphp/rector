<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use Rector\ChangesReporting\Rector\AbstractRector\NotifyingRemovingNodeTrait;
use Rector\PostRector\Rector\AbstractRector\NodeCommandersTrait;

trait AbstractRectorTrait
{
    use RemovedAndAddedFilesTrait;
    use NodeTypeResolverTrait;
    use NameResolverTrait;
    use BetterStandardPrinterTrait;
    use NodeCommandersTrait;
    use SimpleCallableNodeTraverserTrait;
    use ComplexRemovalTrait;
    use NotifyingRemovingNodeTrait;
}
