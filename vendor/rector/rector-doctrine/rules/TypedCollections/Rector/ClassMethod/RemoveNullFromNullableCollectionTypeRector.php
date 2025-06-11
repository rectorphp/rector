<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
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
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove null from a nullable Collection, as empty ArrayCollection is preferred instead to keep property type strict and always a collection', [new CodeSample(<<<'CODE_SAMPLE'
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
        // remove nullable if has one
        if (!$varTagValueNode->type instanceof NullableTypeNode) {
            return null;
        }
        // unwrap nullable type
        $varTagValueNode->type = $varTagValueNode->type->type;
        $phpDocInfo->removeByType(VarTagValueNode::class);
        $phpDocInfo->addTagValueNode($varTagValueNode);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
        return $property;
    }
    private function hasNativeCollectionType(Property $property) : bool
    {
        if (!$property->type instanceof Name) {
            return \false;
        }
        return $this->isName($property->type, DoctrineClass::COLLECTION);
    }
}
