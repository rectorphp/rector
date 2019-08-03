<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

class Param {}

use Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture\Param as Aliased;

class ConstructorParamWithAliasedParam
{
    private $aliasedParam;

    public function __construct(Aliased $aliasedParam)
    {
        $this->aliasedParam = $aliasedParam;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

class Param {}

use Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture\Param as Aliased;

class ConstructorParamWithAliasedParam
{
    /**
     * @var Aliased
     */
    private $aliasedParam;

    public function __construct(Aliased $aliasedParam)
    {
        $this->aliasedParam = $aliasedParam;
    }
}

?>
