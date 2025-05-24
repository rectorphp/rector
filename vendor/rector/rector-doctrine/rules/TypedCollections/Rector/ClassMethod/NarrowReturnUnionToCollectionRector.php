<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
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
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnTagValueNode = $classMethodPhpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return null;
        }
        if ($node->returnType !== null && $this->isName($node->returnType, DoctrineClass::COLLECTION)) {
            $hasNativeCollectionType = \true;
        } else {
            $hasNativeCollectionType = \false;
        }
        $hasChanged = $this->unionCollectionTagValueNodeNarrower->narrow($returnTagValueNode, $hasNativeCollectionType);
        if ($hasChanged === \false) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
}
