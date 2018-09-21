<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Property\PropertyToMethodRector\Wrong;

final class SomeClassWithParameters
{
    private $parameter;

    public function run()
    {
        $value = $this->getConfig('parameter');
    }
}
