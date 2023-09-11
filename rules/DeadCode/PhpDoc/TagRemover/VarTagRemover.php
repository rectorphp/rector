<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc\TagRemover;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\Generic\TemplateObjectWithoutClassType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\DeadCode\PhpDoc\DeadVarTagValueNodeAnalyzer;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
final class VarTagRemover
{
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\DeadVarTagValueNodeAnalyzer
     */
    private $deadVarTagValueNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    public function __construct(DoctrineTypeAnalyzer $doctrineTypeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, DeadVarTagValueNodeAnalyzer $deadVarTagValueNodeAnalyzer, PhpDocTypeChanger $phpDocTypeChanger, DocBlockUpdater $docBlockUpdater)
    {
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->deadVarTagValueNodeAnalyzer = $deadVarTagValueNodeAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function removeVarTagIfUseless(PhpDocInfo $phpDocInfo, Property $property) : bool
    {
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return \false;
        }
        $isVarTagValueDead = $this->deadVarTagValueNodeAnalyzer->isDead($varTagValueNode, $property);
        if (!$isVarTagValueDead) {
            return \false;
        }
        if ($this->phpDocTypeChanger->isAllowed($varTagValueNode->type)) {
            return \false;
        }
        $phpDocInfo->removeByType(VarTagValueNode::class);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
        return \true;
    }
    /**
     * @param \PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $node
     */
    public function removeVarPhpTagValueNodeIfNotComment($node, Type $type) : void
    {
        if ($type instanceof TemplateObjectWithoutClassType) {
            return;
        }
        // keep doctrine collection narrow type
        if ($this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($type)) {
            return;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return;
        }
        // has description? keep it
        if ($varTagValueNode->description !== '') {
            return;
        }
        // keep string[] etc.
        if ($this->phpDocTypeChanger->isAllowed($varTagValueNode->type)) {
            return;
        }
        $phpDocInfo->removeByType(VarTagValueNode::class);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
    }
}
