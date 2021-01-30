<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\TagRemover;

use PhpParser\Node\FunctionLike;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Type\Type;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareReturnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DeadDocBlock\DeadReturnTagValueNodeAnalyzer;

final class ReturnTagRemover
{
    /**
     * @var DeadReturnTagValueNodeAnalyzer
     */
    private $deadReturnTagValueNodeAnalyzer;

    public function __construct(DeadReturnTagValueNodeAnalyzer $deadReturnTagValueNodeAnalyzer)
    {
        $this->deadReturnTagValueNodeAnalyzer = $deadReturnTagValueNodeAnalyzer;
    }

    public function removeReturnTagIfUseless(PhpDocInfo $phpDocInfo, FunctionLike $functionLike): void
    {
        // remove existing type
        $attributeAwareReturnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (! $attributeAwareReturnTagValueNode instanceof AttributeAwareReturnTagValueNode) {
            return;
        }

        $isReturnTagValueDead = $this->deadReturnTagValueNodeAnalyzer->isDead(
            $attributeAwareReturnTagValueNode,
            $functionLike
        );
        if (! $isReturnTagValueDead) {
            return;
        }

        $phpDocInfo->removeByType(ReturnTagValueNode::class);
    }
}
