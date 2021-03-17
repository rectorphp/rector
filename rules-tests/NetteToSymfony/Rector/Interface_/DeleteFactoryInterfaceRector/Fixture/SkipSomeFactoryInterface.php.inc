<?php

declare(strict_types=1);

namespace Rector\Tests\NetteToSymfony\Rector\Interface_\DeleteFactoryInterfaceRector\Fixture;

use Rector\Tests\NetteToSymfony\Rector\Interface_\DeleteFactoryInterfaceRector\Source\SomeControl;

interface SkipSomeFactoryInterface
{
    /**
     * @return SomeControl
     */
    public function create();
}
