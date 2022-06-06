<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Comments\NodeTraverser;

use RectorPrefix20220606\PhpParser\NodeTraverser;
use RectorPrefix20220606\Rector\Comments\NodeVisitor\CommentRemovingNodeVisitor;
final class CommentRemovingNodeTraverser extends NodeTraverser
{
    public function __construct(CommentRemovingNodeVisitor $commentRemovingNodeVisitor)
    {
        $this->addVisitor($commentRemovingNodeVisitor);
    }
}
