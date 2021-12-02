<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

class SkipNonPrivateProperty2
{
    public function __construct(
        public string $name
    ) {
    }

    public function getName()
    {
        return $this->name;
    }
}
