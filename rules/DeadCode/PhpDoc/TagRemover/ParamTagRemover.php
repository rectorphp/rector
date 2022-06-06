<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DeadCode\PhpDoc\TagRemover;

use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Rector\DeadCode\PhpDoc\DeadParamTagValueNodeAnalyzer;
use RectorPrefix20220606\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
final class ParamTagRemover
{
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\DeadParamTagValueNodeAnalyzer
     */
    private $deadParamTagValueNodeAnalyzer;
    public function __construct(DeadParamTagValueNodeAnalyzer $deadParamTagValueNodeAnalyzer)
    {
        $this->deadParamTagValueNodeAnalyzer = $deadParamTagValueNodeAnalyzer;
    }
    public function removeParamTagsIfUseless(PhpDocInfo $phpDocInfo, FunctionLike $functionLike) : bool
    {
        $hasChanged = \false;
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function (Node $docNode) use($functionLike, $phpDocInfo, &$hasChanged) : ?int {
            if (!$docNode instanceof PhpDocTagNode) {
                return null;
            }
            if (!$docNode->value instanceof ParamTagValueNode) {
                return null;
            }
            // handle only basic types, keep phpstan/psalm helper ones
            if ($docNode->name !== '@param') {
                return null;
            }
            if (!$this->deadParamTagValueNodeAnalyzer->isDead($docNode->value, $functionLike)) {
                return null;
            }
            $phpDocInfo->markAsChanged();
            $hasChanged = \true;
            return PhpDocNodeTraverser::NODE_REMOVE;
        });
        return $hasChanged;
    }
}
