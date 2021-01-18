<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\TagRemover;

use PhpParser\Node\FunctionLike;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DeadDocBlock\DeadParamTagValueNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ParamTagRemover
{
    /**
     * @var DeadParamTagValueNodeAnalyzer
     */
    private $deadParamTagValueNodeAnalyzer;

    public function __construct(DeadParamTagValueNodeAnalyzer $deadParamTagValueNodeAnalyzer)
    {
        $this->deadParamTagValueNodeAnalyzer = $deadParamTagValueNodeAnalyzer;
    }

    public function removeParamTagsIfUseless(FunctionLike $functionLike): void
    {
        $phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return;
        }

        foreach ($phpDocInfo->getParamTagValueNodes() as $paramTagValueNode) {
            $paramName = $paramTagValueNode->parameterName;

            // remove existing type

            $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
            if ($paramTagValueNode === null) {
                continue;
            }

            $isParamTagValueDead = $this->deadParamTagValueNodeAnalyzer->isDead($paramTagValueNode, $functionLike);
            if ($isParamTagValueDead === false) {
                continue;
            }

            $phpDocInfo->removeTagValueNodeFromNode($paramTagValueNode);
        }
    }
}
