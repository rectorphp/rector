<?php

declare(strict_types=1);

namespace Rector\AbstractRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\UnionType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://www.php.net/functions.anonymous
 */
abstract class AbstractConvertToAnonymousFunctionRector extends AbstractRector
{
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $body = $this->getBody($node);
        $parameters = $this->getParameters($node);
        $useVariables = $this->resolveUseVariables($body, $parameters);

        $anonymousFunctionNode = new Closure();
        $anonymousFunctionNode->params = $parameters;

        foreach ($useVariables as $useVariable) {
            $anonymousFunctionNode->uses[] = new ClosureUse($useVariable);
        }

        $anonymousFunctionNode->returnType = $this->getReturnType($node);

        if ($body !== []) {
            $anonymousFunctionNode->stmts = $body;
        }

        return $anonymousFunctionNode;
    }

    abstract protected function shouldSkip(Node $node): bool;

    /**
     * @return Param[]
     */
    abstract protected function getParameters(Node $node): array;

    /**
     * @return Identifier|Name|NullableType|UnionType|null
     */
    abstract protected function getReturnType(Node $node): ?Node;

    /**
     * @return Expression[]|Stmt[]
     */
    abstract protected function getBody(Node $node): array;

    /**
     * @param Node[] $nodes
     * @param Param[] $paramNodes
     * @return Variable[]
     */
    private function resolveUseVariables(array $nodes, array $paramNodes): array
    {
        $paramNames = [];
        foreach ($paramNodes as $paramNode) {
            $paramNames[] = $this->getName($paramNode);
        }

        $variableNodes = $this->betterNodeFinder->findInstanceOf($nodes, Variable::class);

        /** @var Variable[] $filteredVariables */
        $filteredVariables = [];
        $alreadyAssignedVariables = [];
        foreach ($variableNodes as $variableNode) {
            // "$this" is allowed
            if ($this->isName($variableNode, 'this')) {
                continue;
            }

            $variableName = $this->getName($variableNode);
            if ($variableName === null) {
                continue;
            }

            if (in_array($variableName, $paramNames, true)) {
                continue;
            }

            $parentNode = $variableNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Assign) {
                $alreadyAssignedVariables[] = $variableName;
            }

            if ($this->isNames($variableNode, $alreadyAssignedVariables)) {
                continue;
            }

            $filteredVariables[$variableName] = $variableNode;
        }

        return $filteredVariables;
    }
}
