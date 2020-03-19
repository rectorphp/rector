<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Rector\AbstractRector;

use PhpParser\Node;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait RectorChangeCollectorTrait
{
    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

    /**
     * @required
     */
    public function autowireAppliedRectorCollectorTrait(RectorChangeCollector $rectorChangeCollector): void
    {
        $this->rectorChangeCollector = $rectorChangeCollector;
    }

    protected function notifyNodeChangeFileInfo(Node $node): void
    {
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo === null) {
            // this file was changed before and this is a sub-new node
            // array Traverse to all new nodes would have to be used, but it's not worth the performance
            return;
        }

        $this->rectorChangeCollector->addRectorClassWithLine(static::class, $fileInfo, $node->getLine());
    }
}
