<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use RectorPrefix202208\Tracy\Dumper;
if (!\function_exists('dump_with_depth')) {
    /**
     * @param mixed $value
     */
    function dump_with_depth($value, int $depth = 2) : void
    {
        Dumper::dump($value, [Dumper::DEPTH => $depth]);
    }
}
if (!\function_exists('dn')) {
    function dn(Node $node, int $depth = 2) : void
    {
        \RectorPrefix202208\dump_node($node, $depth);
    }
}
if (!\function_exists('dump_node')) {
    /**
     * @param \PhpParser\Node|mixed[] $node
     */
    function dump_node($node, int $depth = 2) : void
    {
        $nodes = \is_array($node) ? $node : [$node];
        foreach ($nodes as $node) {
            Dumper::dump($node, [Dumper::DEPTH => $depth]);
        }
    }
}
if (!\function_exists('print_node')) {
    /**
     * @param \PhpParser\Node|mixed[] $node
     */
    function print_node($node) : void
    {
        $standard = new Standard();
        $nodes = \is_array($node) ? $node : [$node];
        foreach ($nodes as $node) {
            $printedContent = $standard->prettyPrint([$node]);
            Dumper::dump($printedContent);
        }
    }
}
