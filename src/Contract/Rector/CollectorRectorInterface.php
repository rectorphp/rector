<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Rector;

use PHPStan\Node\CollectedDataNode;
/**
 * @api
 */
interface CollectorRectorInterface extends \Rector\Core\Contract\Rector\RectorInterface
{
    public function setCollectedDataNode(CollectedDataNode $collectedDataNode) : void;
    public function getCollectedDataNode() : CollectedDataNode;
}
