<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector\Source\Entity;

final class SameClassImplementEntity implements RandomInterface
{
    public function returnAnother(): RandomInterface
    {
    }
}
