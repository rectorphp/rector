<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\Fixture;

final class PrivateProperty
{
    public $name;

    public function getName(): string|null
    {
        return $this->name;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\Fixture;

final class PrivateProperty
{
    public ?string $name = null;

    public function getName(): string|null
    {
        return $this->name;
    }
}

?>
