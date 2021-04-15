<?php

declare(strict_types=1);

namespace Rector\Tests\Autodiscovery\Rector\Interface_\MoveInterfacesToContractNamespaceDirectoryRector\Source;

use Rector\Tests\Autodiscovery\Rector\Interface_\MoveInterfacesToContractNamespaceDirectoryRector\Source\Contract\RandomInterface;

class RandomInterfaceUseCase
{
    public \Rector\Tests\Autodiscovery\Rector\Interface_\MoveInterfacesToContractNamespaceDirectoryRector\Source\Contract\RandomInterface $random;

    public function create(): \Rector\Tests\Autodiscovery\Rector\Interface_\MoveInterfacesToContractNamespaceDirectoryRector\Source\Contract\RandomInterface
    {
    }
}
