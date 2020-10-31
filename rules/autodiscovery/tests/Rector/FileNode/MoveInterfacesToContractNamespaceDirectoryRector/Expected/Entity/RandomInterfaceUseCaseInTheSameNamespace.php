<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Entity;

class RandomInterfaceUseCaseInTheSameNamespace
{
    public \Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Contract\RandomInterface $random;

    public function create(): \Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Contract\RandomInterface
    {
    }
}
