<?php

declare(strict_types=1);

namespace Rector\Tests\Autodiscovery\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source;

use Rector\Tests\Autodiscovery\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Entity\RandomInterface;

class RandomInterfaceUseCase
{
    /**
     * @var RandomInterface
     */
    public $random;

    public function create(): RandomInterface
    {
    }
}
