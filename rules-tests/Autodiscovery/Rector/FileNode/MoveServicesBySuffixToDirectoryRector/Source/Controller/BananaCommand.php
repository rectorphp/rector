<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileNode\MoveServicesBySuffixToDirectoryRector\Source\Controller;

final class BananaCommand
{
    public function run()
    {
        return new Orange();
    }
}
