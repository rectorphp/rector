<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector\Source;

use Rector\Autodiscovery\Tests\Rector\FileSystem\MoveEntitiesToEntityDirectoryRector\Source\Entity\RandomInterface;

class SomeFactory
{
    public function create(): RandomInterface
    {
    }
}
