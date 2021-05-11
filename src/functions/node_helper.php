<?php

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use Tracy\Dumper;

if (! function_exists('dn')) {
    function dn(Node $node, int $depth = 2): void
    {
        dump_node($node, $depth);
    }
}


if (! function_exists('dump_node')) {
    /**
     * @param Node|Node[] $node
     */
    function dump_node(Node | array $node, int $depth = 2): void
    {
        $nodes = is_array($node) ? $node : [$node];

        foreach ($nodes as $node) {
            Dumper::dump($node, [
                Dumper::DEPTH => $depth,
            ]);
        }
    }
}


if (! function_exists('print_node')) {
    /**
     * @param Node|Node[] $node
     */
    function print_node(Node | array $node): void
    {
        $standard = new Standard();

        $nodes = is_array($node) ? $node : [$node];

        foreach ($nodes as $node) {
            $printedContent = $standard->prettyPrint([$node]);
            Dumper::dump($printedContent);
        }
    }
}
