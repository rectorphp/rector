<?php

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector\Fixture;

class SkipAnonymous
{
    public function __construct()
    {
        $x = new class()
        {
            public function hydrate($context, array $data, $object)
            {
                return $object;
            }
        };
    }
}
