<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\TypeContext;

final class VariableTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
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
        return Variable::class;
    }

    /**
     * @param Variable $variableNode
     */
    public function resolve(Node $variableNode): ?string
    {
        return null;

        $variableType = null;

        $parentNode = $variableNode->getAttribute(Attribute::PARENT_NODE);

        if ($parentNode instanceof Assign) {
//            $variableType = $this->processVariableTypeForAssign($variableNode, $parentNode);
            return $this->nodeTypeResolver->resolve($variableNode, $parentNode);
        } elseif ($variableNode->name instanceof Variable) {
            // nested: ${$type}[$name] - dynamic, unable to resolve type
            return null;
        }

        $variableType = $this->typeContext->getTypeForVariable((string) $variableNode->name);

        if ($variableNode->name === 'this') {
            return $variableNode->getAttribute(Attribute::CLASS_NAME);
        }

        if ($variableType) {
            $variableNode->setAttribute(Attribute::TYPE, $variableType);
        }

        return $variableType;
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
}
