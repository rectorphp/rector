<?php

declare (strict_types=1);
namespace Rector\MockeryToProphecy\Collector;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class MockVariableCollector
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @return array<string, class-string>
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\StaticCall $node
     */
    public function collectMockVariableName($node) : array
    {
        $mockVariableTypesByNames = [];
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Expr\Assign) {
            return [];
        }
        if (!$parentNode->var instanceof \PhpParser\Node\Expr\Variable) {
            return [];
        }
        /** @var Variable $variable */
        $variable = $parentNode->var;
        /** @var string $variableName */
        $variableName = $this->nodeNameResolver->getName($variable);
        if (!isset($node->args[0])) {
            return [];
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return [];
        }
        $type = $node->args[0]->value;
        $mockedType = $this->valueResolver->getValue($type);
        $mockVariableTypesByNames[$variableName] = $mockedType;
        return $mockVariableTypesByNames;
    }
}
