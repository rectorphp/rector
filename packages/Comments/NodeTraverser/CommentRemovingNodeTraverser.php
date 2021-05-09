<?php

declare (strict_types=1);
namespace Rector\Comments\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\Comments\NodeVisitor\CommentRemovingNodeVisitor;
final class CommentRemovingNodeTraverser extends \PhpParser\NodeTraverser
{
    public function __construct(\Rector\Comments\NodeVisitor\CommentRemovingNodeVisitor $commentRemovingNodeVisitor)
    {
        $this->addVisitor($commentRemovingNodeVisitor);
    }
}
