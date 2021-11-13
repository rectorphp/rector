<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\Fixture;

final class PublicPropertyWithNullDefault
{
    public $name;

    public function getName(): string
    {
        return $this->name;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\Fixture;

final class PublicPropertyWithNullDefault
{
    public ?string $name = null;

    public function getName(): string
    {
        return $this->name;
    }
}

?>
