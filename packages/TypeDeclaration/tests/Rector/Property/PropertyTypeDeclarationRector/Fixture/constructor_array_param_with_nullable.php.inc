<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

class ConstructorArrayParamWithNullable
{
    private $data;

    public function __construct(?array $data)
    {
        $this->data = $data;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

class ConstructorArrayParamWithNullable
{
    /**
     * @var mixed[]|null
     */
    private $data;

    public function __construct(?array $data)
    {
        $this->data = $data;
    }
}

?>
