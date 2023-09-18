<?php

declare (strict_types=1);
namespace Rector\Php80\DocBlock;

use PhpParser\Comment;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class PropertyPromotionDocBlockMerger
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, PhpDocTypeChanger $phpDocTypeChanger, VarTagRemover $varTagRemover, PhpDocInfoPrinter $phpDocInfoPrinter, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->varTagRemover = $varTagRemover;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function mergePropertyAndParamDocBlocks(Property $property, Param $param, ?ParamTagValueNode $paramTagValueNode) : void
    {
        $paramComments = $param->getComments();
        // already has @param tag → give it priority over @var and remove @var
        if ($paramTagValueNode instanceof ParamTagValueNode) {
            $propertyDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if ($propertyDocInfo->hasByType(VarTagValueNode::class)) {
                $propertyDocInfo->removeByType(VarTagValueNode::class);
                $propertyComments = $this->phpDocInfoPrinter->printToComments($propertyDocInfo);
                /** @var Comment[] $mergedComments */
                $mergedComments = \array_merge($paramComments, $propertyComments);
                $mergedComments = $this->removeEmptyComments($mergedComments);
                $param->setAttribute(AttributeKey::COMMENTS, $mergedComments);
            }
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($param);
    }
    public function decorateParamWithPropertyPhpDocInfo(ClassMethod $classMethod, Property $property, Param $param, string $paramName) : void
    {
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $param->setAttribute(AttributeKey::PHP_DOC_INFO, $propertyPhpDocInfo);
        // make sure the docblock is useful
        if ($param->type === null) {
            $varTagValueNode = $propertyPhpDocInfo->getVarTagValueNode();
            if (!$varTagValueNode instanceof VarTagValueNode) {
                return;
            }
            $paramType = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($varTagValueNode, $property);
            $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $this->phpDocTypeChanger->changeParamType($classMethod, $classMethodPhpDocInfo, $paramType, $param, $paramName);
        } else {
            $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        }
        $this->varTagRemover->removeVarPhpTagValueNodeIfNotComment($param, $paramType);
    }
    /**
     * @param Comment[] $mergedComments
     * @return Comment[]
     */
    private function removeEmptyComments(array $mergedComments) : array
    {
        return \array_filter($mergedComments, static function (Comment $comment) : bool {
            return $comment->getText() !== '';
        });
    }
}
