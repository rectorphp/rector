<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\TagRemover;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DeadDocBlock\DeadReturnTagValueNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;

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

    public function removeReturnTagIfUseless(FunctionLike $functionLike): void
    {
        $phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return;
        }

        // remove existing type
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if ($returnTagValueNode === null) {
            return;
        }

        $isReturnTagValueDead = $this->deadReturnTagValueNodeAnalyzer->isDead($returnTagValueNode, $functionLike);
        if ($isReturnTagValueDead === false) {
            return;
        }

        $phpDocInfo->removeTagValueNodeFromNode($returnTagValueNode);
    }
}
