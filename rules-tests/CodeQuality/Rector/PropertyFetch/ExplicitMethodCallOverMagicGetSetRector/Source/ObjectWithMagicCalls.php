<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source;

use Nette\SmartObject;

final class ObjectWithMagicCalls
{
    // adds magic __get() and __set() methods
    use SmartObject;

    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }
}
