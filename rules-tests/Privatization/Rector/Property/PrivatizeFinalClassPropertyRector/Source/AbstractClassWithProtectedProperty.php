<?php

declare(strict_types=1);

namespace Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\Source;

abstract class AbstractClassWithProtectedProperty
{
    /**
     * @var int
     */
    protected $value = 1000;

    public function run()
    {
        static::$valueStatic = 1000;
    }

    public function run2()
    {
        self::$valueStatic2 = 1000;
    }

    public function run3()
    {
        \Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\Fixture\KeepParentStaticProtectedUsedByParent::$valueStatic3 = 1000;
    }
}
