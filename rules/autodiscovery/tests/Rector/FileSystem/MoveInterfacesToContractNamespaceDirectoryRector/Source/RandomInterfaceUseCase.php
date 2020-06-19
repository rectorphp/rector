<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector\Source;

use Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector\Source\Entity\RandomInterface;

class RandomInterfaceUseCase
{
    public RandomInterface $random;

    public function create(): RandomInterface
    {
    }
}
