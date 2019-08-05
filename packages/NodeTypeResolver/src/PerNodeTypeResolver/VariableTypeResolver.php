<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ThisType;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector;
use Rector\NodeTypeResolver\PHPStan\Type\StaticTypeToStringResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class VariableTypeResolver implements PerNodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var StaticTypeToStringResolver
     */
    private $staticTypeToStringResolver;

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
        StaticTypeToStringResolver $staticTypeToStringResolver,
        NameResolver $nameResolver,
        TraitNodeScopeCollector $traitNodeScopeCollector
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->staticTypeToStringResolver = $staticTypeToStringResolver;
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
     * @return string[]
     */
    public function resolve(Node $variableNode): array
    {
        $parentNode = $variableNode->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Param) {
            return $this->nodeTypeResolver->resolve($parentNode);
        }

        $variableName = $this->nameResolver->getName($variableNode);
        if ($variableName === null) {
            return [];
        }

        $scopeType = $this->resolveTypesFromScope($variableNode, $variableName);
        if ($scopeType !== []) {
            return $scopeType;
        }

        // get from annotation
        $varTypeInfo = $this->docBlockManipulator->getVarTypeInfo($variableNode);
        if ($varTypeInfo === null) {
            return [];
        }

        $varType = $varTypeInfo->getFqnType();

        return $varType === null ? [] : [$varType];
    }

    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    private function resolveNodeScope(Node $node): ?Scope
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);

        // is node in trait
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode instanceof Trait_) {
            /** @var string $traitName */
            $traitName = $node->getAttribute(AttributeKey::CLASS_NAME);
            $traitNodeScope = $this->traitNodeScopeCollector->getScopeForTraitAndNode($traitName, $node);
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Node) {
            $parentNodeScope = $parentNode->getAttribute(AttributeKey::SCOPE);
        }

        // get nearest variable scope
        $method = $node->getAttribute(AttributeKey::METHOD_NODE);
        if ($method instanceof Node) {
            $methodNodeScope = $method->getAttribute(AttributeKey::SCOPE);
        }

        return $nodeScope ?? $traitNodeScope ?? $parentNodeScope ?? $methodNodeScope ?? null;
    }

    /**
     * @return string[]
     */
    private function resolveTypesFromScope(Variable $variable, string $variableName): array
    {
        $nodeScope = $this->resolveNodeScope($variable);
        if ($nodeScope === null) {
            return [];
        }

        if ($nodeScope->hasVariableType($variableName) !== TrinaryLogic::createYes()) {
            return [];
        }

        $type = $nodeScope->getVariableType($variableName);

        // this
        if ($type instanceof ThisType) {
            return [$nodeScope->getClassReflection()->getName()];
        }

        return $this->staticTypeToStringResolver->resolveAnyType($type);
    }
}
