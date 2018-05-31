<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\Source;

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
