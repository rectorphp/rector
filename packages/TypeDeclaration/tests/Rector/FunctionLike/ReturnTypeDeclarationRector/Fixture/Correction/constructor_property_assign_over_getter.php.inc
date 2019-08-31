<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Correction;

class ConstructorPropertyAssignOverGetter
{
    private $nullableValue;

    public function __construct(?array $nullableValue)
    {
        $this->nullableValue = $nullableValue;
    }

    public function getNullableValue(): array
    {
        return $this->nullableValue;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Correction;

class ConstructorPropertyAssignOverGetter
{
    private $nullableValue;

    public function __construct(?array $nullableValue)
    {
        $this->nullableValue = $nullableValue;
    }

    public function getNullableValue(): ?array
    {
        return $this->nullableValue;
    }
}

?>
