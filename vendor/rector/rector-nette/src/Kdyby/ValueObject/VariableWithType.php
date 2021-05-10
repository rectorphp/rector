<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PHPStan\Type\Type;
final class VariableWithType
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var Type
     */
    private $type;
    /**
     * @var Identifier|Name|NullableType|UnionType|null
     */
    private $phpParserTypeNode;
    /**
     * @param Identifier|Name|NullableType|UnionType|null $phpParserTypeNode
     */
    public function __construct(string $name, \PHPStan\Type\Type $staticType, ?\PhpParser\Node $phpParserTypeNode)
    {
        $this->name = $name;
        $this->type = $staticType;
        $this->phpParserTypeNode = $phpParserTypeNode;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getType() : \PHPStan\Type\Type
    {
        return $this->type;
    }
    /**
     * @return Identifier|Name|NullableType|UnionType|null
     */
    public function getPhpParserTypeNode() : ?\PhpParser\Node
    {
        return $this->phpParserTypeNode;
    }
}
