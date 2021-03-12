<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

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
