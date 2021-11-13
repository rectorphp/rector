<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\Fixture;

final class PrivatePropertyWithSingleType
{
    private $name;

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

final class PrivatePropertyWithSingleType
{
    private string $name;

    public function getName(): string
    {
        return $this->name;
    }
}

?>
