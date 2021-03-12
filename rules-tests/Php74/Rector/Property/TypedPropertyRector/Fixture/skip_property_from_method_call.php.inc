<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class SkipPropertyFromMethodCall
{
    private $object;

    public function __construct()
    {
        $this->object = $this->createObject();
    }

    private function createObject()
    {
        return new \stdClass();
    }
}
