<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\FixturePhp73;

final class PrivatePropertyWithVarDoc
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

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector\FixturePhp73;

final class PrivatePropertyWithVarDoc
{
    /**
     * @var string
     */
    public $name;

    public function getName(): string
    {
        return $this->name;
    }
}

?>
