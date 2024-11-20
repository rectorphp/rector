<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc\TagRemover;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DeadCode\PhpDoc\DeadReturnTagValueNodeAnalyzer;
final class ReturnTagRemover
{
    /**
     * @readonly
     */
    private DeadReturnTagValueNodeAnalyzer $deadReturnTagValueNodeAnalyzer;
    public function __construct(DeadReturnTagValueNodeAnalyzer $deadReturnTagValueNodeAnalyzer)
    {
        $this->deadReturnTagValueNodeAnalyzer = $deadReturnTagValueNodeAnalyzer;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function removeReturnTagIfUseless(PhpDocInfo $phpDocInfo, $functionLike) : bool
    {
        // remove existing type
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return \false;
        }
        $isReturnTagValueDead = $this->deadReturnTagValueNodeAnalyzer->isDead($returnTagValueNode, $functionLike);
        if (!$isReturnTagValueDead) {
            return \false;
        }
        $phpDocInfo->removeByType(ReturnTagValueNode::class);
        return \true;
    }
}
