<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use RectorPrefix20220418\Tracy\Dumper;
if (!\function_exists('dump_with_depth')) {
    /**
     * @param mixed $value
     */
    function dump_with_depth($value, int $depth = 2) : void
    {
        \RectorPrefix20220418\Tracy\Dumper::dump($value, [\RectorPrefix20220418\Tracy\Dumper::DEPTH => $depth]);
    }
}
if (!\function_exists('dn')) {
    function dn(\PhpParser\Node $node, int $depth = 2) : void
    {
        \RectorPrefix20220418\dump_node($node, $depth);
    }
}
if (!\function_exists('dump_node')) {
    /**
     * @param mixed[]|\PhpParser\Node $node
     */
    function dump_node($node, int $depth = 2) : void
    {
        $nodes = \is_array($node) ? $node : [$node];
        foreach ($nodes as $node) {
            \RectorPrefix20220418\Tracy\Dumper::dump($node, [\RectorPrefix20220418\Tracy\Dumper::DEPTH => $depth]);
        }
    }
}
if (!\function_exists('print_node')) {
    /**
     * @param mixed[]|\PhpParser\Node $node
     */
    function print_node($node) : void
    {
        $standard = new \PhpParser\PrettyPrinter\Standard();
        $nodes = \is_array($node) ? $node : [$node];
        foreach ($nodes as $node) {
            $printedContent = $standard->prettyPrint([$node]);
            \RectorPrefix20220418\Tracy\Dumper::dump($printedContent);
        }
    }
}
