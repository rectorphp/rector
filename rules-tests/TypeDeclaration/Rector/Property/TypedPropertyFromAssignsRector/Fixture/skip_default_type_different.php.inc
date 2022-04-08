<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class SkipDefaultTypeDifferent
{
    private $property = true;

    public function run()
    {
        $this->property = 100;
    }
}
