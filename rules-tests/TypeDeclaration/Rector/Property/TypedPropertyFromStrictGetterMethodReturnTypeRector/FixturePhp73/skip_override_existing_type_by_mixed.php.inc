<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\FixturePhp73;

final class SkipOverrideExistingTypeByMixed
{
    /**
     * @var array<string, bool>
     */
    private $rules = [];

    /**
     * @return array<string, bool>
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}

?>
