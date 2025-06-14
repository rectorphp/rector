<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Doctrine\TypedCollections\NodeModifier\PropertyDefaultNullRemover;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\RemoveNullFromNullableCollectionTypeRector\RemoveNullFromNullableCollectionTypeRectorTest
 */
final class RemoveNullFromNullableCollectionTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private PropertyDefaultNullRemover $propertyDefaultNullRemover;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger, StaticTypeMapper $staticTypeMapper, PropertyDefaultNullRemover $propertyDefaultNullRemover)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->propertyDefaultNullRemover = $propertyDefaultNullRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove null from a nullable Collection, as empty ArrayCollection is preferred instead to keep property/class method type strict and always a collection', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    private $items;

    public function setItems(?Collection $items): void
    {
        $this->items = $items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    private $items;

    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Property::class];
    }
    /**
     * @param ClassMethod|Property $node
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|null
     */
    public function refactor(Node $node)
    {
        if ($node instanceof Property) {
            return $this->refactorProperty($node);
        }
        return $this->refactorClassMethod($node);
    }
    private function refactorClassMethod(ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if (\count($classMethod->params) !== 1) {
            return null;
        }
        // nullable might be on purpose, e.g. via data provider
        if ($this->testsNodeAnalyzer->isInTestClass($classMethod)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($classMethod->params as $param) {
            if (!$param->type instanceof NullableType) {
                continue;
            }
            $realType = $param->type->type;
            if (!$this->isName($realType, DoctrineClass::COLLECTION)) {
                continue;
            }
            $param->type = $realType;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $classMethod;
        }
        return null;
    }
    private function refactorProperty(Property $property) : ?Property
    {
        if ($property->type instanceof NullableType && $this->hasNativeCollectionType($property->type)) {
            // unwrap nullable type
            $property->type = $property->type->type;
            $this->propertyDefaultNullRemover->remove($property);
            return $property;
        }
        if (!$this->hasNativeCollectionType($property)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return null;
        }
        if ($varTagValueNode->type instanceof UnionTypeNode) {
            $hasChanged = \false;
            $unionTypeNode = $varTagValueNode->type;
            foreach ($unionTypeNode->types as $key => $unionedType) {
                if ($unionedType instanceof IdentifierTypeNode && $unionedType->name === 'null') {
                    unset($unionTypeNode->types[$key]);
                    $hasChanged = \true;
                }
            }
            if ($hasChanged) {
                // only one type left, lets use it directly
                if (\count($unionTypeNode->types) === 1) {
                    $onlyType = \array_pop($unionTypeNode->types);
                    $finalType = $onlyType;
                } else {
                    $finalType = $unionTypeNode;
                }
                $finalType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($finalType, $property);
                $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $finalType);
                return $property;
            }
        }
        // remove nullable if has one
        if (!$varTagValueNode->type instanceof NullableTypeNode) {
            return null;
        }
        // unwrap nullable type
        $finalType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type->type, $property);
        $this->phpDocTypeChanger->changeVarType($property, $phpDocInfo, $finalType);
        return $property;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\NullableType $node
     */
    private function hasNativeCollectionType($node) : bool
    {
        if (!$node->type instanceof Name) {
            return \false;
        }
        return $this->isName($node->type, DoctrineClass::COLLECTION);
    }
}
