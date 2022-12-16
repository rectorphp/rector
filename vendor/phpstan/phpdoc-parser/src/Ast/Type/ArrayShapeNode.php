<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;
class ArrayShapeNode implements \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    /** @var ArrayShapeItemNode[] */
    public $items;
    /** @var bool */
    public $sealed;
    public function __construct(array $items, bool $sealed = \true)
    {
        $this->items = $items;
        $this->sealed = $sealed;
    }
    public function __toString() : string
    {
        $items = $this->items;
        if (!$this->sealed) {
            $items[] = '...';
        }
        return 'array{' . implode(', ', $items) . '}';
    }
}
