<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\TagRemover;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\DeadDocBlock\DeadReturnTagValueNodeAnalyzer;

final class ReturnTagRemover
{
    /**
     * @var DeadReturnTagValueNodeAnalyzer
     */
    private $deadReturnTagValueNodeAnalyzer;

    /**
     * @var PhpDocTagRemover
     */
    private $phpDocTagRemover;

    public function __construct(
        DeadReturnTagValueNodeAnalyzer $deadReturnTagValueNodeAnalyzer,
        PhpDocTagRemover $phpDocTagRemover
    ) {
        $this->deadReturnTagValueNodeAnalyzer = $deadReturnTagValueNodeAnalyzer;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }

    public function removeReturnTagIfUseless(PhpDocInfo $phpDocInfo, FunctionLike $functionLike): void
    {
        // remove existing type
        $attributeAwareReturnTagValueNode = $phpDocInfo->getReturnTagValue();
        if ($attributeAwareReturnTagValueNode === null) {
            return;
        }

        $isReturnTagValueDead = $this->deadReturnTagValueNodeAnalyzer->isDead(
            $attributeAwareReturnTagValueNode,
            $functionLike
        );
        if (! $isReturnTagValueDead) {
            return;
        }

        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $attributeAwareReturnTagValueNode);
    }
}
