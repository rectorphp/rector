<?php

declare(strict_types=1);
namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector\Source\Contract;

interface RandomInterface
{
    public function returnAnother(): \Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector\Source\Contract\RandomInterface;
}
