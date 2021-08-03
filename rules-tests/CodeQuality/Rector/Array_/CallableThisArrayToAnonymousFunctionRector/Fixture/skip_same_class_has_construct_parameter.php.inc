<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\Fixture;

final class SkipSameClassHasConstructParameter
{
    private $property;

    public function __construct($property)
    {
        $this->property = $property;
    }

    public function run()
    {
        return [SkipSameClassHasConstructParameter::class, 'someMethod'];
    }

    public function someMethod()
    {

    }
}
?>
