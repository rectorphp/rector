<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;

final class CloningNodeTraverser extends NodeTraverser
{
    public function __construct()
    {
        $this->visitors[] = new CloningVisitor;
    }
}
