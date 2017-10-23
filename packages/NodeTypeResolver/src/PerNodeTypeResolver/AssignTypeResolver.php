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

    public function getNodeClass(): string
    {
        return Assign::class;
    }

    /**
     * @param Assign $assignNode
     */
    public function resolve(Node $assignNode): ?string
    {
        if (! $assignNode->var instanceof Variable) {
            // @todo: resolve for properties etc. as well
            return null;
        }

        $variableType = $this->resolveTypeForRightSide($assignNode);

        if ($variableType) {
            /** @var Variable $variableNode */
            $variableNode = $assignNode->var;
            $variableName = $variableNode->name;
            $this->typeContext->addVariableWithType($variableName, $variableType);
        }

        return $variableType;
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    private function processAssignVariableNode(Assign $assignNode): ?string
    {
        if ($assignNode->var->name instanceof Variable) {
            $name = $assignNode->var->name->name;
        } else {
            $name = $assignNode->var->name;
        }

        $this->typeContext->addAssign($name, $assignNode->expr->name);

        return $this->typeContext->getTypeForVariable($name);
    }

    private function resolveTypeForRightSide(Assign $assignNode): ?string
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

        return null;
    }
}
