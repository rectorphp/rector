<?php

declare (strict_types=1);
namespace Rector\Comments\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\Comments\NodeVisitor\CommentRemovingNodeVisitor;
final class CommentRemovingNodeTraverser extends NodeTraverser
{
    public function __construct(CommentRemovingNodeVisitor $commentRemovingNodeVisitor)
    {
        parent::__construct($commentRemovingNodeVisitor);
    }
}
