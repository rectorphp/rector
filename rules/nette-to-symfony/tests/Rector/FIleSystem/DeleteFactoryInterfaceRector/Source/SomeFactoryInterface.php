<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\FIleSystem\DeleteFactoryInterfaceRector\Source;

interface SomeFactoryInterface
{
    /**
     * @return SomeControl
     */
    public function create();
}
