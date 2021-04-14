<?php

declare(strict_types=1);

namespace Rector\Tests\Autodiscovery\Rector\Interface_\MoveInterfacesToContractNamespaceDirectoryRector\Source\Control;

interface FormFactory
{
    /**
     * @return SomeForm
     */
    public function create();
}
