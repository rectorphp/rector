<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;

final class CloningNodeTraverser extends NodeTraverser
{
    public function __construct()
    {
        // note: probably have to be recreated to clear cache
        $this->addVisitor(new CloningVisitor);
    }
}
