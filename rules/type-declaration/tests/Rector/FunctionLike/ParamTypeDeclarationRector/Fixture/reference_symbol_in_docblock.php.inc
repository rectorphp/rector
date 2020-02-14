<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture;

class ReferenceSymbolInDocBlock
{
    /**
     * @param array &$value
     */
    public function someFunction(&$value)
    {
    }
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture;

class ReferenceSymbolInDocBlock
{
    /**
     * @param array &$value
     */
    public function someFunction(array &$value)
    {
    }
}
?>
