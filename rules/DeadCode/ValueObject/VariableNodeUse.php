<?php

declare (strict_types=1);
namespace Rector\DeadCode\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class VariableNodeUse
{
    /**
     * @var string
     */
    public const TYPE_USE = 'use';
    /**
     * @var string
     */
    public const TYPE_ASSIGN = 'assign';
    /**
     * @var int
     */
    private $startTokenPosition;
    /**
     * @var string
     */
    private $variableName;
    /**
     * @var string
     */
    private $type;
    /**
     * @var \PhpParser\Node\Expr\Variable
     */
    private $variable;
    /**
     * @var string|null
     */
    private $nestingHash;
    public function __construct(int $startTokenPosition, string $variableName, string $type, \PhpParser\Node\Expr\Variable $variable, ?string $nestingHash = null)
    {
        $this->startTokenPosition = $startTokenPosition;
        $this->variableName = $variableName;
        $this->type = $type;
        $this->variable = $variable;
        $this->nestingHash = $nestingHash;
    }
    public function isName(string $name) : bool
    {
        return $this->variableName === $name;
    }
    public function getStartTokenPosition() : int
    {
        return $this->startTokenPosition;
    }
    public function isType(string $type) : bool
    {
        return $this->type === $type;
    }
    public function getVariableNode() : \PhpParser\Node\Expr\Variable
    {
        return $this->variable;
    }
    public function getParentNode() : \PhpParser\Node
    {
        $parentNode = $this->variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $parentNode;
    }
    public function getNestingHash() : ?string
    {
        return $this->nestingHash;
    }
}
