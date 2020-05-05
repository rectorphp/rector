<?php

declare(strict_types=1);

namespace Rector\Core\Exclusion\Check;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Contract\Exclusion\ExclusionCheckInterface;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ExcludeByDocBlockExclusionCheck implements ExclusionCheckInterface
{
    public function isNodeSkippedByRector(PhpRectorInterface $phpRector, Node $node): bool
    {
        if ($node instanceof PropertyProperty || $node instanceof Const_) {
            $node = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($node === null) {
                return false;
            }
        }
        $comment = $node->getDocComment();
        if ($comment !== null && $this->checkCommentForIgnore($phpRector, $comment)) {
            return true;
        }

        // recurse up until a Stmt node is found since it might contain a noRector
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $node instanceof Stmt && $parentNode !== null) {
            return $this->isNodeSkippedByRector($phpRector, $parentNode);
        }

        return false;
    }

    private function checkCommentForIgnore(PhpRectorInterface $phpRector, Doc $doc): bool
    {
        $regex = '#@noRector\s*\\\\?' . preg_quote(get_class($phpRector), '/') . '#i';
        return (bool) Strings::match($doc->getText(), $regex);
    }
}
