<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;
class DoctrineAnnotation implements Node
{
    use NodeAttributes;
    public string $name;
    /** @var list<DoctrineArgument> */
    public array $arguments;
    /**
     * @param list<DoctrineArgument> $arguments
     */
    public function __construct(string $name, array $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }
    public function __toString(): string
    {
        $arguments = implode(', ', $this->arguments);
        return $this->name . '(' . $arguments . ')';
    }
    /**
     * @param array<string, mixed> $properties
     */
    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['name'], $properties['arguments']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }
        return $instance;
    }
}
