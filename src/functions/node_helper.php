<?php

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use Tracy\Dumper;

function dump_node(Node $node, int $depth = 2): void
{
    Dumper::dump($node, [
        Dumper::DEPTH => $depth,
    ]);
}

/**
 * @param Node|Node[] $node
 */
function print_node($node): void
{
    $standard = new Standard();

    if (is_array($node)) {
        foreach ($node as $singleNode) {
            $printedContent = $standard->prettyPrint([$singleNode]);
            Dumper::dump($printedContent);
        }
    } else {
        $printedContent = $standard->prettyPrint([$node]);
        Dumper::dump($printedContent);
    }
}
