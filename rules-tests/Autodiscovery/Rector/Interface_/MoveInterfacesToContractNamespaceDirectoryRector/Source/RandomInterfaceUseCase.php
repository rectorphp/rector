<?php

declare(strict_types=1);

namespace Rector\Tests\Autodiscovery\Rector\Interface_\MoveInterfacesToContractNamespaceDirectoryRector\Source;

use Rector\Tests\Autodiscovery\Rector\Interface_\MoveInterfacesToContractNamespaceDirectoryRector\Source\Entity\RandomInterface;

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
