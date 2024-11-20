<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;
class ArrayShapeNode implements \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    public const KIND_ARRAY = 'array';
    public const KIND_LIST = 'list';
    public const KIND_NON_EMPTY_ARRAY = 'non-empty-array';
    public const KIND_NON_EMPTY_LIST = 'non-empty-list';
    use NodeAttributes;
    /** @var ArrayShapeItemNode[] */
    public array $items;
    public bool $sealed;
    /** @var self::KIND_* */
    public $kind;
    public ?\PHPStan\PhpDocParser\Ast\Type\ArrayShapeUnsealedTypeNode $unsealedType = null;
    /**
     * @param ArrayShapeItemNode[] $items
     * @param self::KIND_* $kind
     */
    private function __construct(array $items, bool $sealed = \true, ?\PHPStan\PhpDocParser\Ast\Type\ArrayShapeUnsealedTypeNode $unsealedType = null, string $kind = self::KIND_ARRAY)
    {
        $this->items = $items;
        $this->sealed = $sealed;
        $this->unsealedType = $unsealedType;
        $this->kind = $kind;
    }
    /**
     * @param ArrayShapeItemNode[] $items
     * @param self::KIND_* $kind
     */
    public static function createSealed(array $items, string $kind = self::KIND_ARRAY) : self
    {
        return new self($items, \true, null, $kind);
    }
    /**
     * @param ArrayShapeItemNode[] $items
     * @param self::KIND_* $kind
     */
    public static function createUnsealed(array $items, ?\PHPStan\PhpDocParser\Ast\Type\ArrayShapeUnsealedTypeNode $unsealedType, string $kind = self::KIND_ARRAY) : self
    {
        return new self($items, \false, $unsealedType, $kind);
    }
    public function __toString() : string
    {
        $items = $this->items;
        if (!$this->sealed) {
            $items[] = '...' . $this->unsealedType;
        }
        return $this->kind . '{' . implode(', ', $items) . '}';
    }
}
