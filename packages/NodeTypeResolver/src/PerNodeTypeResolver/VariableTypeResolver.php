<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector;
use Rector\PhpParser\Node\Resolver\NameResolver;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\VariableTypeResolverTest
 */
final class VariableTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var TraitNodeScopeCollector
     */
    private $traitNodeScopeCollector;

    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        NameResolver $nameResolver,
        TraitNodeScopeCollector $traitNodeScopeCollector
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->nameResolver = $nameResolver;
        $this->traitNodeScopeCollector = $traitNodeScopeCollector;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Variable::class];
    }

    /**
     * @param Variable $variableNode
     */
    public function resolve(Node $variableNode): Type
    {
        $parentNode = $variableNode->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Param) {
            return $this->nodeTypeResolver->resolve($parentNode);
        }

        $variableName = $this->nameResolver->getName($variableNode);
        if ($variableName === null) {
            return new MixedType();
        }

        $scopeType = $this->resolveTypesFromScope($variableNode, $variableName);
        if (! $scopeType instanceof MixedType) {
            return $scopeType;
        }

        // get from annotation
        return $this->docBlockManipulator->getVarType($variableNode);
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    private function resolveTypesFromScope(Variable $variable, string $variableName): Type
    {
        $nodeScope = $this->resolveNodeScope($variable);
        if ($nodeScope === null) {
            return new MixedType();
        }

        if (! $nodeScope->hasVariableType($variableName)->yes()) {
            return new MixedType();
        }

        // this → object type is easier to work with and consistent with the rest of the code
        return $nodeScope->getVariableType($variableName);
    }

    private function resolveNodeScope(Node $node): ?Scope
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        if ($nodeScope) {
            return $nodeScope;
        }

        // is node in trait
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode instanceof Trait_) {
            /** @var string $traitName */
            $traitName = $node->getAttribute(AttributeKey::CLASS_NAME);
            $traitNodeScope = $this->traitNodeScopeCollector->getScopeForTraitAndNode($traitName, $node);
            if ($traitNodeScope) {
                return $traitNodeScope;
            }
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Node) {
            $parentNodeScope = $parentNode->getAttribute(AttributeKey::SCOPE);
            if ($parentNodeScope) {
                return $parentNodeScope;
            }
        }

        // get nearest variable scope
        $method = $node->getAttribute(AttributeKey::METHOD_NODE);
        if ($method instanceof Node) {
            $methodNodeScope = $method->getAttribute(AttributeKey::SCOPE);
            if ($methodNodeScope) {
                return $methodNodeScope;
            }
        }

        return null;
    }
}
