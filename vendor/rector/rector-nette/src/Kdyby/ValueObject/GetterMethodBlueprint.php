<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\ValueObject;

use PhpParser\Node;
final class GetterMethodBlueprint
{
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var \PhpParser\Node|null
     */
    private $returnTypeNode;
    /**
     * @var string
     */
    private $variableName;
    public function __construct(string $methodName, ?\PhpParser\Node $returnTypeNode, string $variableName)
    {
        $this->methodName = $methodName;
        $this->returnTypeNode = $returnTypeNode;
        $this->variableName = $variableName;
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function getReturnTypeNode() : ?\PhpParser\Node
    {
        return $this->returnTypeNode;
    }
    public function getVariableName() : string
    {
        return $this->variableName;
    }
}
