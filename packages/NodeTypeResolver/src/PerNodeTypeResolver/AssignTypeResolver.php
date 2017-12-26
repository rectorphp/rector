<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeContext;

final class AssignTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(TypeContext $typeContext)
    {
        $this->typeContext = $typeContext;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        // special case of expression
        // maybe move to variable?
        // inspire in printer, how it's solved there
        return [Assign::class];
    }

    /**
     * @param Assign $assignNode
     * @return string[]
     */
    public function resolve(Node $assignNode): array
    {
        if (! $assignNode->var instanceof Variable) {
            // @todo: resolve for properties etc. as well
            return [];
        }

        $variableTypes = $this->resolveTypeForRightSide($assignNode);

        if ($variableTypes) {
            /** @var Variable $variableNode */
            $variableNode = $assignNode->var;
            $variableName = (string) $variableNode->name;
            $this->typeContext->addVariableWithTypes($variableName, $variableTypes);
        }

        return $variableTypes;
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @return string[]
     */
    private function processAssignVariableNode(Assign $assignNode): array
    {
        /** @var Variable $variableNode */
        $variableNode = $assignNode->var;

        if ($variableNode->name instanceof Variable) {
            $name = (string) $variableNode->name->name;
        } else {
            $name = (string) $variableNode->name;
        }

        /** @var Variable $otherVariableNode */
        $otherVariableNode = $assignNode->expr;
        $otherVariableName = (string) $otherVariableNode->name;

        $this->typeContext->addAssign($name, $otherVariableName);

        return $this->typeContext->getTypesForVariable($name);
    }

    /**
     * @return string[]
     */
    private function resolveTypeForRightSide(Assign $assignNode): array
    {
        // $var = $anotherVar;
        if ($assignNode->expr instanceof Variable) {
            return $this->processAssignVariableNode($assignNode);
        }

        // $var = $this->someMethod();
        if ($assignNode->expr instanceof MethodCall) {
            return $this->nodeTypeResolver->resolve($assignNode->expr);
        }

        // $var = new (...);
        if ($assignNode->expr instanceof New_) {
            return $this->nodeTypeResolver->resolve($assignNode->expr);
        }

        return [];
    }

    public function isPrimary(): bool
    {
        return false;
    }
}
