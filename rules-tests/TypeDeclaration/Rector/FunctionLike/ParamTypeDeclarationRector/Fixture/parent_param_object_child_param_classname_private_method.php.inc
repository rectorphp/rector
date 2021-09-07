<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class ParentParamObjectChildParamClassNamePrivateMethod
{
    /**
     * @param object $obj
     */
    private function someFunction($obj)
    {
    }
}

class ChildParamClassNamePrivateMethod extends ParentParamObjectChildParamClassNamePrivateMethod
{
    /**
     * @param \stdClass $obj
     */
    private function someFunction($obj)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class ParentParamObjectChildParamClassNamePrivateMethod
{
    private function someFunction(object $obj)
    {
    }
}

class ChildParamClassNamePrivateMethod extends ParentParamObjectChildParamClassNamePrivateMethod
{
    private function someFunction(\stdClass $obj)
    {
    }
}

?>
