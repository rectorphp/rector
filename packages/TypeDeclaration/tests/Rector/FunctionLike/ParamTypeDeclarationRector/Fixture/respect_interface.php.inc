<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\ComplextArray;

interface ParentInterface
{
    /**
     * @param mixed $value
     */
    public function __construct($value);
}

class ImplementingClass implements ParentInterface
{
    /**
     * @param string $value
     */
    public function __construct($value) {}
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\ComplextArray;

interface ParentInterface
{
    /**
     * @param mixed $value
     */
    public function __construct($value);
}

class ImplementingClass implements ParentInterface
{
    /**
     * @param string $value
     */
    public function __construct($value) {}
}

?>
