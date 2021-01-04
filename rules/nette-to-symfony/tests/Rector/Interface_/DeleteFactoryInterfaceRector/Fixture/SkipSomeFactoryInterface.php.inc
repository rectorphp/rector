<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Interface_\DeleteFactoryInterfaceRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\Interface_\DeleteFactoryInterfaceRector\Source\SomeControl;

interface SkipSomeFactoryInterface
{
    /**
     * @return SomeControl
     */
    public function create();
}
