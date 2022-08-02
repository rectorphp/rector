<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc\TagRemover;

use PhpParser\Node\FunctionLike;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DeadCode\PhpDoc\DeadParamTagValueNodeAnalyzer;
use RectorPrefix202208\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
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
    public function removeParamTagsIfUseless(PhpDocInfo $phpDocInfo, FunctionLike $functionLike) : void
    {
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function (Node $docNode) use($functionLike, $phpDocInfo) : ?int {
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
            return PhpDocNodeTraverser::NODE_REMOVE;
        });
    }
}
