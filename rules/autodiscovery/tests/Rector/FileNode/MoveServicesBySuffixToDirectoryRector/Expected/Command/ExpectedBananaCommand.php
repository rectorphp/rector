<?php

declare(strict_types=1);
namespace Rector\Autodiscovery\Tests\Rector\FileNode\MoveServicesBySuffixToDirectoryRector\Source\Command;

use Rector\Autodiscovery\Tests\Rector\FileNode\MoveServicesBySuffixToDirectoryRector\Source\Controller\Orange;
final class BananaCommand
{
    public function run()
    {
        return new Orange();
    }
}
