<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\PhpDocParser\BetterPhpDocParser;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use ReflectionProperty;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\NameTypeResolver\NameTypeResolverTest
 */
final class PropertyFetchTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterPhpDocParser
     */
    private $betterPhpDocParser;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var TraitNodeScopeCollector
     */
    private $traitNodeScopeCollector;

    public function __construct(
        BetterPhpDocParser $betterPhpDocParser,
        NodeNameResolver $nodeNameResolver,
        ParsedNodeCollector $parsedNodeCollector,
        StaticTypeMapper $staticTypeMapper,
        TraitNodeScopeCollector $traitNodeScopeCollector
    ) {
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterPhpDocParser = $betterPhpDocParser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->traitNodeScopeCollector = $traitNodeScopeCollector;
    }

    /**
     * @required
     */
    public function autowirePropertyFetchTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
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
        $vendorPropertyType = $this->getVendorPropertyFetchType($node);
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

    private function getVendorPropertyFetchType(PropertyFetch $propertyFetch): Type
    {
        $varObjectType = $this->nodeTypeResolver->resolve($propertyFetch->var);
        if (! $varObjectType instanceof TypeWithClassName) {
            return new MixedType();
        }

        $class = $this->parsedNodeCollector->findClass($varObjectType->getClassName());
        if ($class !== null) {
            return new MixedType();
        }

        // 3rd party code
        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
        if ($propertyName === null) {
            return new MixedType();
        }

        if (! property_exists($varObjectType->getClassName(), $propertyName)) {
            return new MixedType();
        }

        // property is used
        $reflectionProperty = new ReflectionProperty($varObjectType->getClassName(), $propertyName);
        if (! $reflectionProperty->getDocComment()) {
            return new MixedType();
        }

        $phpDocNode = $this->betterPhpDocParser->parseString((string) $reflectionProperty->getDocComment());
        $varTagValues = $phpDocNode->getVarTagValues();

        if (! isset($varTagValues[0])) {
            return new MixedType();
        }

        $typeNode = $varTagValues[0]->type;
        if (! $typeNode instanceof TypeNode) {
            return new MixedType();
        }

        return $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($typeNode, new Nop());
    }
}
