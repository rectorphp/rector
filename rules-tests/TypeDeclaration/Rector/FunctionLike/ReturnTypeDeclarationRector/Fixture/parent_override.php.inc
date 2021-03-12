<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Rector\Core\Exception\NotImplementedYetException;

abstract class ParentOverride
{
    public function getValue()
    {
        throw new NotImplementedYetException();
    }
}

class OverrideParent extends ParentOverride
{
    public function getValue()
    {
        return 'string';
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Rector\Core\Exception\NotImplementedYetException;

abstract class ParentOverride
{
    public function getValue()
    {
        throw new NotImplementedYetException();
    }
}

class OverrideParent extends ParentOverride
{
    public function getValue(): string
    {
        return 'string';
    }
}

?>
