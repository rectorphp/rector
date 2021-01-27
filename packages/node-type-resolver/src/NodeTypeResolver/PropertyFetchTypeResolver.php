<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use Nette\Utils\FileSystem;
use PhpParser\Node;
use PhpParser\Node\Expr\Property;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\ParserFactory;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\PropertyFetchTypeResolverTest
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
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var TraitNodeScopeCollector
     */
    private $traitNodeScopeCollector;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        ParsedNodeCollector $parsedNodeCollector,
        StaticTypeMapper $staticTypeMapper,
        TraitNodeScopeCollector $traitNodeScopeCollector,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->traitNodeScopeCollector = $traitNodeScopeCollector;
        $this->betterNodeFinder = $betterNodeFinder;
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

        if (! $scope instanceof Scope) {
            $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
            if ($classNode instanceof Trait_) {
                /** @var string $traitName */
                $traitName = $classNode->getAttribute(AttributeKey::CLASS_NAME);

                $scope = $this->traitNodeScopeCollector->getScopeForTraitAndNode($traitName, $node);
            }
        }

        if (! $scope instanceof Scope) {
            $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
            // fallback to class, since property fetches are not scoped by PHPStan
            if ($classNode instanceof ClassLike) {
                $scope = $classNode->getAttribute(AttributeKey::SCOPE);
            }

            if (! $scope instanceof Scope) {
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

        $phpDocInfo = $propertyFetch->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            $classReflection = $varObjectType->getClassReflection();
            if (! $classReflection instanceof ClassReflection) {
                return $varObjectType;
            }

            $phpParser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
            $nodes = $phpParser->parse(FileSystem::read($classReflection->getFileName()));

            $propertyProperty = $this->betterNodeFinder->findFirst($nodes, function (Node $node) use (
                $propertyName
            ): bool {
                if (! $node instanceof PropertyProperty) {
                    return false;
                }
                return $node->name->toString() === $propertyName;
                return $node instanceof PropertyProperty && $node->name->toString() === $propertyName;
            });

            if (! $propertyProperty instanceof PropertyProperty) {
                return new MixedType();
            }

            $phpDocInfo = $propertyProperty->getAttribute(AttributeKey::PHP_DOC_INFO);
            if (! $phpDocInfo instanceof PhpDocInfo) {
                return new MixedType();
            }
        }

        $tagValueNode = $phpDocInfo->getVarTagValueNode();
        if (! $tagValueNode instanceof VarTagValueNode) {
            return new MixedType();
        }

        $typeNode = $tagValueNode->type;
        if (! $typeNode instanceof TypeNode) {
            return new MixedType();
        }

        return $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($typeNode, new Nop());
    }
}
