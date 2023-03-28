<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeTraverser;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
final class NodeConnectingTraverser extends NodeTraverser
{
    public function __construct()
    {
        parent::__construct();
        $this->addVisitor(new NodeConnectingVisitor());
    }
}
