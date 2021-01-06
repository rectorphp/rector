<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\NameTypeResolver\NameTypeResolverTest
 */
final class PropertyFetchTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var TraitNodeScopeCollector
     */
    private $traitNodeScopeCollector;

    /**
     * @var VendorPropertyFetchTypeResolver
     */
    private $vendorPropertyFetchTypeResolver;

    public function __construct(
        TraitNodeScopeCollector $traitNodeScopeCollector,
        VendorPropertyFetchTypeResolver $vendorPropertyFetchTypeResolver
    ) {
        $this->traitNodeScopeCollector = $traitNodeScopeCollector;
        $this->vendorPropertyFetchTypeResolver = $vendorPropertyFetchTypeResolver;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [PropertyFetch::class];
    }

    /**
     * @param PropertyFetch $node
     */
    public function resolve(Node $node): Type
    {
        // compensate 3rd party non-analysed property reflection
        $vendorPropertyType = $this->vendorPropertyFetchTypeResolver->resolve($node);
        if (! $vendorPropertyType instanceof MixedType) {
            return $vendorPropertyType;
        }

        /** @var Scope|null $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        if ($scope === null) {
            $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
            if ($classNode instanceof Trait_) {
                /** @var string $traitName */
                $traitName = $classNode->getAttribute(AttributeKey::CLASS_NAME);

                /** @var Scope|null $scope */
                $scope = $this->traitNodeScopeCollector->getScopeForTraitAndNode($traitName, $node);
            }
        }

        if ($scope === null) {
            $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
            // fallback to class, since property fetches are not scoped by PHPStan
            if ($classNode instanceof ClassLike) {
                $scope = $classNode->getAttribute(AttributeKey::SCOPE);
            }

            if ($scope === null) {
                return new MixedType();
            }
        }

        return $scope->getType($node);
    }
}
