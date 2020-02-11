<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

use stdClass;

class ConstructorParamWithNullable
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

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

use stdClass;

class ConstructorParamWithNullable
{
    /**
     * @var string|null
     */
    private $nullableString;

    public function __construct(string $nullableString = null)
    {
        $this->nullableString = $nullableString;
    }
}

?>
