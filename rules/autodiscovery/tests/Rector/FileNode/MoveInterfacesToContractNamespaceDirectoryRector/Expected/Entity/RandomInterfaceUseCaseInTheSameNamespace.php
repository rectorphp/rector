<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Entity;

use Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Contract\RandomInterface;
class RandomInterfaceUseCaseInTheSameNamespace
{
    /**
     * @var RandomInterface
     */
    public $random;

    public function create(): RandomInterface
    {
    }
}
