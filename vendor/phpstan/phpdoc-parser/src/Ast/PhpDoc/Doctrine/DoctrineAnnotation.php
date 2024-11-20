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
    public function __toString() : string
    {
        $arguments = implode(', ', $this->arguments);
        return $this->name . '(' . $arguments . ')';
    }
}
