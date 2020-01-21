<?php

declare(strict_types=1);

namespace Rector\NodeDumper;

use PhpParser\Node;
use Tracy\Debugger;

final class NodeDumper
{
    public static function dumpNode(Node $node): void
    {
        $nodePropertise = get_object_vars($node);
        foreach ($nodePropertise as $nodeProperty) {
            if (! $nodeProperty instanceof Node) {
                continue;
            }

            // remove attributes on subnodes
            $nodeProperty->setAttributes([]);
        }

        Debugger::dump($node);
    }
}
