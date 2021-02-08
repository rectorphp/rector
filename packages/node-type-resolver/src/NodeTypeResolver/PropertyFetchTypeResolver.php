<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Property;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\VarLikeIdentifier;
use PhpParser\Parser;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyFetchTypeResolver\PropertyFetchTypeResolverTest
 */
final class PropertyFetchTypeResolver implements NodeTypeResolverInterface
{
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
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        StaticTypeMapper $staticTypeMapper,
        TraitNodeScopeCollector $traitNodeScopeCollector,
        SmartFileSystem $smartFileSystem,
        BetterNodeFinder $betterNodeFinder,
        Parser $parser,
        NodeRepository $nodeRepository
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->traitNodeScopeCollector = $traitNodeScopeCollector;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->smartFileSystem = $smartFileSystem;
        $this->parser = $parser;
        $this->nodeRepository = $nodeRepository;
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

        $class = $this->nodeRepository->findClass($varObjectType->getClassName());
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
        if (! $phpDocInfo instanceof PhpDocInfo && $varObjectType instanceof ObjectType) {
            return $this->getPropertyPropertyResolution($varObjectType, $propertyName);
        }

        return $this->getTypeFromPhpDocInfo($phpDocInfo);
    }

    private function getPropertyPropertyResolution(ObjectType $varObjectType, string $propertyName): Type
    {
        $classReflection = $varObjectType->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return $varObjectType;
        }

        if ($classReflection->isBuiltIn()) {
            return $varObjectType;
        }

        $nodes = $this->parser->parse($this->smartFileSystem->readFile((string) $classReflection->getFileName()));
        $propertyProperty = $this->betterNodeFinder->findFirst($nodes, function (Node $node) use (
            $propertyName
        ): bool {
            if (! $node instanceof PropertyProperty) {
                return false;
            }

            if (! $node->name instanceof VarLikeIdentifier) {
                return false;
            }

            return $node->name->toString() === $propertyName;
        });

        if ($propertyProperty instanceof PropertyProperty) {
            return $this->nodeTypeResolver->resolve($propertyProperty);
        }

        return new MixedType();
    }

    private function getTypeFromPhpDocInfo(PhpDocInfo $phpDocInfo): Type
    {
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
