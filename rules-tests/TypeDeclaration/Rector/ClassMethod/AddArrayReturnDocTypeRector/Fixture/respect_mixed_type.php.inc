<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class RespectMixedType
{
    /**
     * @var mixed[]
     */
    private $items = [];

    public function process($message): array
    {
        return $this->items[$message];
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class RespectMixedType
{
    /**
     * @var mixed[]
     */
    private $items = [];

    /**
     * @return mixed[]
     */
    public function process($message): array
    {
        return $this->items[$message];
    }
}

?>
