<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileNode\MoveServicesBySuffixToDirectoryRector\Source\Command;

final class MissPlacedController
{
    public function getSelf()
    {
        return new MissPlacedController;
    }
}
