<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\Fixture;

final class SkipDocType
{
    public $name;

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }
}
