<?php

declare(strict_types=1);
namespace Rector\Tests\Autodiscovery\Rector\Class_\MoveServicesBySuffixToDirectoryRector\Source\Command;

final class BananaCommand
{
    public function run()
    {
        return new \Rector\Tests\Autodiscovery\Rector\Class_\MoveServicesBySuffixToDirectoryRector\Source\Controller\Orange();
    }
}
