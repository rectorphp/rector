<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source;

final class SimpleObject
{
    private $name;

    public function getName()
    {
        return $this->name;
    }
}
