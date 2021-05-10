<?php

declare(strict_types=1);

namespace Rector\DeadCode\PhpDoc\TagRemover;

use PhpParser\Node\FunctionLike;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DeadCode\PhpDoc\DeadReturnTagValueNodeAnalyzer;

final class ReturnTagRemover
{
    public function __construct(
        private DeadReturnTagValueNodeAnalyzer $deadReturnTagValueNodeAnalyzer
    ) {
    }

    public function removeReturnTagIfUseless(PhpDocInfo $phpDocInfo, FunctionLike $functionLike): void
    {
        // remove existing type
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (! $returnTagValueNode instanceof ReturnTagValueNode) {
            return;
        }

        $isReturnTagValueDead = $this->deadReturnTagValueNodeAnalyzer->isDead($returnTagValueNode, $functionLike);
        if (! $isReturnTagValueDead) {
            return;
        }

        $phpDocInfo->removeByType(ReturnTagValueNode::class);
    }
}
