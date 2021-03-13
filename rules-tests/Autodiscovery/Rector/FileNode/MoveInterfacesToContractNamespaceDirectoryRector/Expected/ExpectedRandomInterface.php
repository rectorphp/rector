<?php

declare(strict_types=1);
namespace Rector\Tests\Autodiscovery\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Contract;

interface RandomInterface
{
    public function returnAnother(): \Rector\Tests\Autodiscovery\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Contract\RandomInterface;
}
