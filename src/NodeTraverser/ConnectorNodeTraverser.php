<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\NodeVisitor\NodeConnector;

final class ConnectorNodeTraverser extends NodeTraverser
{
    public function __construct(NodeConnector $nodeConnector)
    {
        $this->addVisitor($nodeConnector);
    }
}
