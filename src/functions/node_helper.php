<?php

declare (strict_types=1);
namespace RectorPrefix202402;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
if (!\function_exists('print_node')) {
    /**
     * @param Node|Node[] $node
     */
    function print_node($node) : void
    {
        $standard = new Standard();
        $nodes = \is_array($node) ? $node : [$node];
        foreach ($nodes as $node) {
            $printedContent = $standard->prettyPrint([$node]);
            \var_dump($printedContent);
        }
    }
}
