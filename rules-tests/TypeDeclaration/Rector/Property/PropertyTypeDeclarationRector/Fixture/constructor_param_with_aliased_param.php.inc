<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Source\ObjectToBeAliased as Aliased;

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

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Source\ObjectToBeAliased as Aliased;

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
