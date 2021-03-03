<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\ValueObject;

use Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Entity\RandomInterface;
final class SameClassImplementEntity implements RandomInterface
{
    public function __construct(RandomInterface $random)
    {
    }
    public function returnAnother(): RandomInterface
    {
    }
}
