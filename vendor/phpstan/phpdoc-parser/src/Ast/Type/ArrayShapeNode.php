<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;
class ArrayShapeNode implements \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    public const KIND_ARRAY = 'array';
    public const KIND_LIST = 'list';
    use NodeAttributes;
    /** @var ArrayShapeItemNode[] */
    public $items;
    /** @var bool */
    public $sealed;
    /** @var self::KIND_* */
    public $kind;
    /**
     * @param ArrayShapeItemNode[] $items
     * @param self::KIND_* $kind
     */
    public function __construct(array $items, bool $sealed = \true, string $kind = self::KIND_ARRAY)
    {
        $this->items = $items;
        $this->sealed = $sealed;
        $this->kind = $kind;
    }
    public function __toString() : string
    {
        $items = $this->items;
        if (!$this->sealed) {
            $items[] = '...';
        }
        return $this->kind . '{' . implode(', ', $items) . '}';
    }
}
