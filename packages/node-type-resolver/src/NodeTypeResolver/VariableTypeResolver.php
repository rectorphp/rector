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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
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

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        TraitNodeScopeCollector $traitNodeScopeCollector,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->traitNodeScopeCollector = $traitNodeScopeCollector;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Variable::class];
    }

    /**
     * @param Variable $node
     */
    public function resolve(Node $node): Type
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Param) {
            return $this->nodeTypeResolver->resolve($parent);
        }

        $variableName = $this->nodeNameResolver->getName($node);
        if ($variableName === null) {
            return new MixedType();
        }

        $scopeType = $this->resolveTypesFromScope($node, $variableName);
        if (! $scopeType instanceof MixedType) {
            return $scopeType;
        }

        // get from annotation
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        return $phpDocInfo->getVarType();
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

        if (! $nodeScope instanceof Scope) {
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
            if (! $parentNodeScope instanceof Scope) {
                continue;
            }

            return $parentNodeScope;
        }
        return null;
    }
}
