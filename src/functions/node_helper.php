<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use RectorPrefix202308\Tracy\Dumper;
// @deprecated, use dump() or dd() instead
if (!\function_exists('dump_node')) {
    /**
     * @return never
     * @param mixed $variable
     */
    function dump_node($variable, int $depth = 2)
    {
        \trigger_error('This function is deprecated, to avoid enforcing of Rector debug package. Use your own favorite debugging package instead');
        exit;
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
