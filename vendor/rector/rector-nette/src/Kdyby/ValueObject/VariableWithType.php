<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\Kdyby\ValueObject;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\ComplexType;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\UnionType;
use RectorPrefix20220606\PHPStan\Type\Type;
final class VariableWithType
{
    /**
     * @readonly
     * @var string
     */
    private $name;
    /**
     * @readonly
     * @var \PHPStan\Type\Type
     */
    private $type;
    /**
     * @var ComplexType|Identifier|Name|NullableType|UnionType|null
     */
    private $phpParserTypeNode;
    /**
     * @param ComplexType|Identifier|Name|NullableType|UnionType|null $phpParserTypeNode
     */
    public function __construct(string $name, Type $type, $phpParserTypeNode)
    {
        $this->name = $name;
        $this->type = $type;
        $this->phpParserTypeNode = $phpParserTypeNode;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getType() : Type
    {
        return $this->type;
    }
    /**
     * @return ComplexType|Identifier|Name|NullableType|UnionType|null
     */
    public function getPhpParserTypeNode() : ?Node
    {
        return $this->phpParserTypeNode;
    }
}
