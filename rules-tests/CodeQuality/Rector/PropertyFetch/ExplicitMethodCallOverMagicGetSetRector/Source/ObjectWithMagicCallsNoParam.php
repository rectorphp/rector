<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source;

use Nette\SmartObject;

final class ObjectWithMagicCallsNoParam
{
    // adds magic __get() and __set() methods
    use SmartObject;

    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName()
    {
        $this->name = 'test';
    }
}
