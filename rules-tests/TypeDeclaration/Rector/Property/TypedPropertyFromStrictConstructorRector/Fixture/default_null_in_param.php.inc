<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

final class DefaultNullInParam
{
    private $nullableString;

    public function __construct(string $nullableString = null)
    {
        $this->nullableString = $nullableString;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

final class DefaultNullInParam
{
    private ?string $nullableString;

    public function __construct(string $nullableString = null)
    {
        $this->nullableString = $nullableString;
    }
}

?>
