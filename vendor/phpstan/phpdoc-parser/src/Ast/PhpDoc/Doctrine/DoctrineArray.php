<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;
class DoctrineArray implements Node
{
    use NodeAttributes;
    /** @var list<DoctrineArrayItem> */
    public array $items;
    /**
     * @param list<DoctrineArrayItem> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }
    public function __toString(): string
    {
        $items = implode(', ', $this->items);
        return '{' . $items . '}';
    }
    /**
     * @param array<string, mixed> $properties
     */
    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['items']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }
        return $instance;
    }
}
