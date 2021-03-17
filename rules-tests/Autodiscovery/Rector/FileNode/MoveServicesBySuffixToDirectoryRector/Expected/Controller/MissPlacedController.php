<?php

declare(strict_types=1);
namespace Rector\Tests\Autodiscovery\Rector\FileNode\MoveServicesBySuffixToDirectoryRector\Source\Controller;

final class MissPlacedController
{
    public function getSelf()
    {
        return new \Rector\Tests\Autodiscovery\Rector\FileNode\MoveServicesBySuffixToDirectoryRector\Source\Controller\MissPlacedController();
    }
}
