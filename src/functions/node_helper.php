<?php

declare (strict_types=1);
namespace RectorPrefix20210609;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use RectorPrefix20210609\Tracy\Dumper;
if (!\function_exists('dn')) {
    /**
     * @return void
     */
    function dn(\PhpParser\Node $node, int $depth = 2)
    {
        \RectorPrefix20210609\dump_node($node, $depth);
    }
}
if (!\function_exists('dump_node')) {
    /**
     * @param Node|Node[] $node
     * @return void
     */
    function dump_node($node, int $depth = 2)
    {
        $nodes = \is_array($node) ? $node : [$node];
        foreach ($nodes as $node) {
            \RectorPrefix20210609\Tracy\Dumper::dump($node, [\RectorPrefix20210609\Tracy\Dumper::DEPTH => $depth]);
        }
    }
}
if (!\function_exists('print_node')) {
    /**
     * @param Node|Node[] $node
     * @return void
     */
    function print_node($node)
    {
        $standard = new \PhpParser\PrettyPrinter\Standard();
        $nodes = \is_array($node) ? $node : [$node];
        foreach ($nodes as $node) {
            $printedContent = $standard->prettyPrint([$node]);
            \RectorPrefix20210609\Tracy\Dumper::dump($printedContent);
        }
    }
}
