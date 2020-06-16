<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Source\ObjectToBeAliased as Aliased;

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

namespace Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\Property\PropertyTypeDeclarationRector\Source\ObjectToBeAliased as Aliased;

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
