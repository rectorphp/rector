<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class SkipSetTypeByParamDoc
{
    private $property;

    /**
     * @param int $property
     */
    public function __construct($property)
    {
        $this->property = $property;
    }
}
