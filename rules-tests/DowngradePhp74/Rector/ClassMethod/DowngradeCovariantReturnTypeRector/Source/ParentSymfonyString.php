<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source;

abstract class ParentSymfonyString
{
    /**
     * @return static
     */
    abstract public function upper(): self;
}
