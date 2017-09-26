<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\NodeTraverser;
use Rector\Rector\RectorCollector;

final class MainNodeTraverser extends NodeTraverser
{
    public function __construct(RectorCollector $rectorCollector)
    {
        foreach ($rectorCollector->getRectors() as $rector) {
            $this->addVisitor($rector);
        }
    }
}
