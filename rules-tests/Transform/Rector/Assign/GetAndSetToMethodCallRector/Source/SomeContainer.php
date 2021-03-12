<?php

declare(strict_types=1);

namespace Rector\Tests\Transform\Rector\Assign\GetAndSetToMethodCallRector\Source;

final class SomeContainer
{
    public $parameters;

    public function addService($name, $service)
    {

    }

    public function getService($name)
    {

    }
}
