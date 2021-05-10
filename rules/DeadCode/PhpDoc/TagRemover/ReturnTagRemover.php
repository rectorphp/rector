<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc\TagRemover;

use PhpParser\Node\FunctionLike;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DeadCode\PhpDoc\DeadReturnTagValueNodeAnalyzer;
final class ReturnTagRemover
{
    /**
     * @var \Rector\DeadCode\PhpDoc\DeadReturnTagValueNodeAnalyzer
     */
    private $deadReturnTagValueNodeAnalyzer;
    public function __construct(\Rector\DeadCode\PhpDoc\DeadReturnTagValueNodeAnalyzer $deadReturnTagValueNodeAnalyzer)
    {
        $this->deadReturnTagValueNodeAnalyzer = $deadReturnTagValueNodeAnalyzer;
    }
    public function removeReturnTagIfUseless(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\FunctionLike $functionLike) : void
    {
        // remove existing type
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode) {
            return;
        }
        $isReturnTagValueDead = $this->deadReturnTagValueNodeAnalyzer->isDead($returnTagValueNode, $functionLike);
        if (!$isReturnTagValueDead) {
            return;
        }
        $phpDocInfo->removeByType(\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode::class);
    }
}
