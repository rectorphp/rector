<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\FixturePhp73;


final class ConstructorArrayParamWithNullable
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

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\FixturePhp73;


final class ConstructorArrayParamWithNullable
{
    /**
     * @var null|mixed[]
     */
    private $data;

    public function __construct(?array $data)
    {
        $this->data = $data;
    }
}

?>
