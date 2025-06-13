<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Doctrine\TypedCollections\DocBlockProcessor\UnionCollectionTagValueNodeNarrower;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\NarrowReturnUnionToCollectionRector\NarrowReturnUnionToCollectionRectorTest
 */
final class NarrowReturnUnionToCollectionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private UnionCollectionTagValueNodeNarrower $unionCollectionTagValueNodeNarrower;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, UnionCollectionTagValueNodeNarrower $unionCollectionTagValueNodeNarrower)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->unionCollectionTagValueNodeNarrower = $unionCollectionTagValueNodeNarrower;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Narrow union type to Collection type in method docblock', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    /**
     * @return Collection|array
     */
    public function getItems()
    {
        return [];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

final class SomeClass
{
    /**
     * @return Collection
     */
    public function getItems()
    {
        return [];
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?ClassMethod
    {
        $hasChanged = \false;
        if ($this->refactorReturnDocblockTag($node)) {
            $hasChanged = \true;
        }
        if ($this->refactorNativeReturn($node)) {
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function refactorReturnDocblockTag(ClassMethod $classMethod) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return \false;
        }
        $hasNativeCollectionType = $this->hasNativeCollectionType($classMethod);
        $hasChanged = $this->unionCollectionTagValueNodeNarrower->narrow($returnTagValueNode, $hasNativeCollectionType);
        if ($hasChanged === \false) {
            return \false;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
        return \true;
    }
    private function hasNativeCollectionType(ClassMethod $classMethod) : bool
    {
        if (!$classMethod->returnType instanceof Node) {
            return \false;
        }
        return $this->isName($classMethod->returnType, DoctrineClass::COLLECTION);
    }
    private function refactorNativeReturn(ClassMethod $classMethod) : bool
    {
        if (!$classMethod->returnType instanceof Node) {
            return \false;
        }
        if ($classMethod->returnType instanceof NullableType && $this->isName($classMethod->returnType->type, DoctrineClass::COLLECTION)) {
            // unwrap nullable type
            $classMethod->returnType = $classMethod->returnType->type;
            return \true;
        }
        if (!$classMethod->returnType instanceof UnionType) {
            return \false;
        }
        $unionType = $classMethod->returnType;
        if (!$this->hasNativeReturnCollectionType($unionType)) {
            return \false;
        }
        // remove null from union type
        foreach ($unionType->types as $key => $unionedType) {
            if ($this->isName($unionedType, 'null')) {
                unset($unionType->types[$key]);
            }
        }
        return \true;
    }
    private function hasNativeReturnCollectionType(UnionType $unionType) : bool
    {
        foreach ($unionType->types as $unionedType) {
            if ($this->isName($unionedType, DoctrineClass::COLLECTION)) {
                return \true;
            }
        }
        return \false;
    }
}
