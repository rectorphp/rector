<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\Source\Control;

interface ControlFactory
{
    public function create(): SomeControl;
}
