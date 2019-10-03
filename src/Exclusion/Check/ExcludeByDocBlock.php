<?php
declare(strict_types=1);

namespace Rector\Exclusion\Check;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use Rector\Contract\Exclusion\ExclusionCheckInterface;
use Rector\Contract\Rector\PhpRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ExcludeByDocBlock implements ExclusionCheckInterface
{
    public function shouldExcludeRector(PhpRectorInterface $phpRector, Node $node): bool
    {
        $comment = $node->getDocComment();
        if ($comment !== null && $this->checkCommentForIgnore($phpRector, $comment)) {
            return true;
        }

        // recurse up until a Stmt node is found since it might contain a noRector
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $node instanceof Stmt && $parentNode !== null) {
            return $this->shouldExcludeRector($phpRector, $parentNode);
        }

        return false;
    }

    private function checkCommentForIgnore(PhpRectorInterface $phpRector, Doc $doc): bool
    {
        if (preg_match_all('#@noRector\s*(?<rectorName>\S+)#i', $doc->getText(), $matches)) {
            foreach ($matches['rectorName'] as $ignoreSpec) {
                if (get_class($phpRector) === ltrim($ignoreSpec, '\\')) {
                    return true;
                }
            }
        }
        return false;
    }
}
