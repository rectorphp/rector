<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\ValueObject;

use PhpParser\Node;

final class GetterMethodBlueprint
{
    /**
     * @var string
     */
    private $methodName;

    /**
     * @var string
     */
    private $variableName;

    /**
     * @var Node|null
     */
    private $returnTypeNode;

    public function __construct(string $methodName, ?Node $returnTypeNode, string $variableName)
    {
        $this->methodName = $methodName;
        $this->returnTypeNode = $returnTypeNode;
        $this->variableName = $variableName;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getReturnTypeNode(): ?Node
    {
        return $this->returnTypeNode;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }
}
