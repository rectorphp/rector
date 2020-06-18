<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector\Source\Control;

interface FormFactory
{
    public function create(): SomeForm;
}
