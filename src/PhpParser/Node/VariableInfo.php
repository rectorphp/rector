<?php declare(strict_types=1);

namespace Rector\PhpParser\Node;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;

final class VariableInfo
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|TypeNode
     */
    private $type;

    /**
     * @param string|TypeNode $type
     */
    public function __construct(string $name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return TypeNode|string
     */
    public function getType()
    {
        return $this->type;
    }

    public function getTypeAsString(): string
    {
        if (is_string($this->type)) {
            return $this->type;
        }

        return (string) $this->type;
    }
}
