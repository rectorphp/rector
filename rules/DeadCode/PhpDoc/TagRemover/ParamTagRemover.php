<?php

declare(strict_types=1);

namespace Rector\DeadCode\PhpDoc\TagRemover;

use PhpParser\Node\FunctionLike;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\DeadCode\PhpDoc\DeadParamTagValueNodeAnalyzer;

final class ParamTagRemover
{
    public function __construct(
        private DeadParamTagValueNodeAnalyzer $deadParamTagValueNodeAnalyzer,
        private PhpDocTagRemover $phpDocTagRemover
    ) {
    }

    public function removeParamTagsIfUseless(PhpDocInfo $phpDocInfo, FunctionLike $functionLike): void
    {
        foreach ($phpDocInfo->getParamTagValueNodes() as $paramTagValueNode) {
            $paramName = $paramTagValueNode->parameterName;

            // remove existing type

            $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);

            if (! $paramTagValueNode instanceof ParamTagValueNode) {
                continue;
            }

            $isParamTagValueDead = $this->deadParamTagValueNodeAnalyzer->isDead($paramTagValueNode, $functionLike);
            if (! $isParamTagValueDead) {
                continue;
            }

            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $paramTagValueNode);
        }
    }
}
