<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\VariableTypeResolver\VariableTypeResolverTest
 */
final class VariableTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var string[]
     */
    private const PARENT_NODE_ATTRIBUTES = [AttributeKey::PARENT_NODE, AttributeKey::METHOD_NODE];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var TraitNodeScopeCollector
     */
    private $traitNodeScopeCollector;

    public function __construct(NodeNameResolver $nodeNameResolver, TraitNodeScopeCollector $traitNodeScopeCollector)
    {
        $this->nodeNameResolver = $nodeNameResolver;
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

        $variableName = $this->nodeNameResolver->getName($variableNode);
        if ($variableName === null) {
            return new MixedType();
        }

        $scopeType = $this->resolveTypesFromScope($variableNode, $variableName);
        if (! $scopeType instanceof MixedType) {
            return $scopeType;
        }

        // get from annotation
        $phpDocInfo = $variableNode->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo instanceof PhpDocInfo) {
            $phpDocInfo->getVarType();
        }

        return new MixedType();
    }

    /**
     * @required
     */
    public function autowireVariableTypeResolver(NodeTypeResolver $nodeTypeResolver): void
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

    private function resolveNodeScope(Variable $variable): ?Scope
    {
        /** @var Scope|null $nodeScope */
        $nodeScope = $variable->getAttribute(AttributeKey::SCOPE);
        if ($nodeScope !== null) {
            return $nodeScope;
        }

        // is node in trait
        $classLike = $variable->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike instanceof Trait_) {
            /** @var string $traitName */
            $traitName = $variable->getAttribute(AttributeKey::CLASS_NAME);
            $traitNodeScope = $this->traitNodeScopeCollector->getScopeForTraitAndNode($traitName, $variable);

            if ($traitNodeScope !== null) {
                return $traitNodeScope;
            }
        }

        return $this->resolveFromParentNodes($variable);
    }

    private function resolveFromParentNodes(Variable $variable): ?Scope
    {
        foreach (self::PARENT_NODE_ATTRIBUTES as $parentNodeAttribute) {
            $parentNode = $variable->getAttribute($parentNodeAttribute);
            if (! $parentNode instanceof Node) {
                continue;
            }

            $parentNodeScope = $parentNode->getAttribute(AttributeKey::SCOPE);
            if ($parentNodeScope === null) {
                continue;
            }

            return $parentNodeScope;
        }
        return null;
    }
}
