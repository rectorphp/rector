<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Entity;

interface RandomInterface
{
    public function returnAnother(): RandomInterface;
}
