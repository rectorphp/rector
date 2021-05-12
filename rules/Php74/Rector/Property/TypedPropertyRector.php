<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;
use Rector\VendorLocker\VendorLockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/typed_properties_v2#proposal
 *
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\TypedPropertyRectorTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\ClassLikeTypesOnlyTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\DoctrineTypedPropertyRectorTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\ImportedTest
 * @see \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\UnionTypedPropertyRectorTest
 */
final class TypedPropertyRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASS_LIKE_TYPE_ONLY = 'class_like_type_only';
    /**
     * Useful for refactoring of huge applications. Taking types first narrows scope
     * @var bool
     */
    private $classLikeTypeOnly = \false;
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer
     */
    private $propertyTypeInferer;
    /**
     * @var \Rector\VendorLocker\VendorLockResolver
     */
    private $vendorLockResolver;
    /**
     * @var \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;
    /**
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer $propertyTypeInferer, \Rector\VendorLocker\VendorLockResolver $vendorLockResolver, \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer $doctrineTypeAnalyzer, \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover $varTagRemover, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->propertyTypeInferer = $propertyTypeInferer;
        $this->vendorLockResolver = $vendorLockResolver;
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->varTagRemover = $varTagRemover;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes property `@var` annotations from annotation to type.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var int
     */
    private count;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private int count;
}
CODE_SAMPLE
, [self::CLASS_LIKE_TYPE_ONLY => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES)) {
            return null;
        }
        // type is already set → skip
        if ($node->type !== null) {
            return null;
        }
        // skip multiple properties
        if (\count($node->props) > 1) {
            return null;
        }
        $varType = $this->propertyTypeInferer->inferProperty($node);
        if ($varType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        if ($varType instanceof \PHPStan\Type\UnionType) {
            $types = $varType->getTypes();
            if (\count($types) === 2 && $types[0] instanceof \PHPStan\Type\Generic\TemplateType) {
                $node->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($types[0]->getBound(), \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::KIND_PROPERTY);
                return $node;
            }
        }
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::KIND_PROPERTY);
        if ($this->isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn($propertyTypeNode, $node)) {
            return null;
        }
        $this->varTagRemover->removeVarPhpTagValueNodeIfNotComment($node, $varType);
        $this->removeDefaultValueForDoctrineCollection($node, $varType);
        $this->addDefaultValueNullForNullableType($node, $varType);
        $node->type = $propertyTypeNode;
        dump_node($node);
        return $node;
    }
    public function configure(array $configuration) : void
    {
        $this->classLikeTypeOnly = $configuration[self::CLASS_LIKE_TYPE_ONLY] ?? \false;
    }
    /**
     * @param Name|NullableType|PhpParserUnionType|null $node
     */
    private function isNullOrNonClassLikeTypeOrMixedOrVendorLockedIn(?\PhpParser\Node $node, \PhpParser\Node\Stmt\Property $property) : bool
    {
        if (!$node instanceof \PhpParser\Node) {
            return \true;
        }
        // is not class-type and should be skipped
        if ($this->shouldSkipNonClassLikeType($node)) {
            return \true;
        }
        // false positive
        if (!$node instanceof \PhpParser\Node\Name) {
            return $this->vendorLockResolver->isPropertyTypeChangeVendorLockedIn($property);
        }
        if (!$this->isName($node, 'mixed')) {
            return $this->vendorLockResolver->isPropertyTypeChangeVendorLockedIn($property);
        }
        return \true;
    }
    /**
     * @param Name|NullableType|PhpParserUnionType $node
     */
    private function shouldSkipNonClassLikeType(\PhpParser\Node $node) : bool
    {
        // unwrap nullable type
        if ($node instanceof \PhpParser\Node\NullableType) {
            $node = $node->type;
        }
        $typeName = $this->getName($node);
        if ($typeName === 'null') {
            return \true;
        }
        if ($typeName === null) {
            return \false;
        }
        if ($typeName === 'callable') {
            return \true;
        }
        if (!$this->classLikeTypeOnly) {
            return \false;
        }
        return !$this->reflectionProvider->hasClass($typeName);
    }
    private function removeDefaultValueForDoctrineCollection(\PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $propertyType) : void
    {
        if (!$this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($propertyType)) {
            return;
        }
        $onlyProperty = $property->props[0];
        $onlyProperty->default = null;
    }
    private function addDefaultValueNullForNullableType(\PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $propertyType) : void
    {
        if (!$propertyType instanceof \PHPStan\Type\UnionType) {
            return;
        }
        if (!$propertyType->isSuperTypeOf(new \PHPStan\Type\NullType())->yes()) {
            return;
        }
        $onlyProperty = $property->props[0];
        // skip is already has value
        if ($onlyProperty->default !== null) {
            return;
        }
        $onlyProperty->default = $this->nodeFactory->createNull();
    }
}
