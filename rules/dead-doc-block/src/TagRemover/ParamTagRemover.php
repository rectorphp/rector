<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\TagRemover;

use PhpParser\Node\FunctionLike;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\DeadDocBlock\DeadParamTagValueNodeAnalyzer;

final class ParamTagRemover
{
    /**
     * @var DeadParamTagValueNodeAnalyzer
     */
    private $deadParamTagValueNodeAnalyzer;

    /**
     * @var PhpDocTagRemover
     */
    private $phpDocTagRemover;

    public function __construct(
        DeadParamTagValueNodeAnalyzer $deadParamTagValueNodeAnalyzer,
        PhpDocTagRemover $phpDocTagRemover
    ) {
        $this->deadParamTagValueNodeAnalyzer = $deadParamTagValueNodeAnalyzer;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }

    public function removeParamTagsIfUseless(PhpDocInfo $phpDocInfo, FunctionLike $functionLike): void
    {
        foreach ($phpDocInfo->getParamTagValueNodes() as $paramTagValueNode) {
            $paramName = $paramTagValueNode->parameterName;

            // remove existing type

            $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
            if ($paramTagValueNode === null) {
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
