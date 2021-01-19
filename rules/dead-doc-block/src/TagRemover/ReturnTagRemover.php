<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\TagRemover;

use PhpParser\Node\FunctionLike;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
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
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(
        DeadReturnTagValueNodeAnalyzer $deadReturnTagValueNodeAnalyzer,
        PhpDocTagRemover $phpDocTagRemover,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->deadReturnTagValueNodeAnalyzer = $deadReturnTagValueNodeAnalyzer;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
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
