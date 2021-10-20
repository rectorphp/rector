<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc\TagRemover;

use PhpParser\Node\FunctionLike;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DeadCode\PhpDoc\DeadParamTagValueNodeAnalyzer;
use RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
final class ParamTagRemover
{
    /**
     * @var \Rector\DeadCode\PhpDoc\DeadParamTagValueNodeAnalyzer
     */
    private $deadParamTagValueNodeAnalyzer;
    public function __construct(\Rector\DeadCode\PhpDoc\DeadParamTagValueNodeAnalyzer $deadParamTagValueNodeAnalyzer)
    {
        $this->deadParamTagValueNodeAnalyzer = $deadParamTagValueNodeAnalyzer;
    }
    public function removeParamTagsIfUseless(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\FunctionLike $functionLike) : void
    {
        $phpDocNodeTraverser = new \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($phpDocInfo->getPhpDocNode(), '', function (\PHPStan\PhpDocParser\Ast\Node $docNode) use($functionLike, $phpDocInfo) : ?int {
            if (!$docNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode) {
                return null;
            }
            if (!$docNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode) {
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
            return \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser::NODE_REMOVE;
        });
    }
}
