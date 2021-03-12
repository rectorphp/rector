<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\ValueObject;

final class SameClassImplementEntity implements \Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Entity\RandomInterface
{
    public function __construct(\Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Entity\RandomInterface $random)
    {
    }
    public function returnAnother(): \Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Entity\RandomInterface
    {
    }
}
