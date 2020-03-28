<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture;

class InferFromPropertyType
{
    private int $id;

    public function setId($id)
    {
        $this->id = $id;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture;

class InferFromPropertyType
{
    private int $id;

    public function setId(int $id)
    {
        $this->id = $id;
    }
}

?>
