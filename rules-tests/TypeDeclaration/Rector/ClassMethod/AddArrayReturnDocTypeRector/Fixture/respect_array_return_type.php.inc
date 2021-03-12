<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class RespectArrayReturnType
{
    /**
     * @var mixed[]
     */
    private $items = [];

    public function process($message): array
    {
        if ($message) {
            return $this->items[$message];
        }

        return [
            'ids' => [],
            'count' => 0,
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class RespectArrayReturnType
{
    /**
     * @var mixed[]
     */
    private $items = [];

    /**
     * @return mixed[]|array<string, mixed[]>|array<string, int>
     */
    public function process($message): array
    {
        if ($message) {
            return $this->items[$message];
        }

        return [
            'ids' => [],
            'count' => 0,
        ];
    }
}

?>
