<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\TagRemover;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\DeadDocBlock\DeadReturnTagValueNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ReturnTagRemover
{
    /**
     * @var DeadReturnTagValueNodeAnalyzer
     */
    private $deadReturnTagValueNodeAnalyzer;

    /**
     * @var PhpDocTagRemover
     */
    private $phpDocRemover;

    public function __construct(
        DeadReturnTagValueNodeAnalyzer $deadReturnTagValueNodeAnalyzer,
        PhpDocTagRemover $phpDocRemover
    ) {
        $this->deadReturnTagValueNodeAnalyzer = $deadReturnTagValueNodeAnalyzer;
        $this->phpDocRemover = $phpDocRemover;
    }

    public function removeReturnTagIfUseless(FunctionLike $functionLike): void
    {
        $phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return;
        }

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

        $this->phpDocRemover->removeTagValueFromNode($phpDocInfo, $attributeAwareReturnTagValueNode);
    }
}
