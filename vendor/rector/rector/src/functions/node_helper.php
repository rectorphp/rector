<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use RectorPrefix20210510\Tracy\Dumper;
function dn(\PhpParser\Node $node, int $depth = 2) : void
{
    \RectorPrefix20210510\dump_node($node, $depth);
}
function dump_node(\PhpParser\Node $node, int $depth = 2) : void
{
    \RectorPrefix20210510\Tracy\Dumper::dump($node, [\RectorPrefix20210510\Tracy\Dumper::DEPTH => $depth]);
}
/**
 * @param Node|Node[] $node
 */
function print_node($node) : void
{
    $standard = new \PhpParser\PrettyPrinter\Standard();
    if (\is_array($node)) {
        foreach ($node as $singleNode) {
            $printedContent = $standard->prettyPrint([$singleNode]);
            \RectorPrefix20210510\Tracy\Dumper::dump($printedContent);
        }
    } else {
        $printedContent = $standard->prettyPrint([$node]);
        \RectorPrefix20210510\Tracy\Dumper::dump($printedContent);
    }
}
