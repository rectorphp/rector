<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Source;

final class OtherClass
{
    private $property;

    public function __construct($property)
    {
        $this->property = $property;
    }

    public function someMethod()
    {
    }
}
