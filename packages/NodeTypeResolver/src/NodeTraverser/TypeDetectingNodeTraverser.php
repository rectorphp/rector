<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\NodeTypeResolver\NodeVisitor\TypeResolvingNodeVisitor;

// @todo: move to normal NodeTraverser as last one? try after setting up tests
final class TypeDetectingNodeTraverser extends NodeTraverser
{
    public function __construct(TypeResolvingNodeVisitor $typeResolvingNodeVisitor)
    {
        $this->visitors[] = $typeResolvingNodeVisitor;
    }
}
