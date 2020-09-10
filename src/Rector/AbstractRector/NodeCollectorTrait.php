<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use Rector\NodeCollector\NodeCollector\NodeRepository;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeCollectorTrait
{
    /**
     * @var NodeRepository
     */
    protected $nodeRepository;

    /**
     * @required
     */
    public function autowireNodeCollectorTrait(NodeRepository $nodeRepository): void
    {
        $this->nodeRepository = $nodeRepository;
    }
}
