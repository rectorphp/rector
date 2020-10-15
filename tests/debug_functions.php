<?php

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;

require __DIR__ . '/../vendor/autoload.php';

/**
 * @param Node|Node[] $node
 */
function print_node($node): void
{
    $standard = new Standard();

    if (is_array($node)) {
        foreach ($node as $singleNode) {
            $printedContent = $standard->prettyPrint([$singleNode]);
            dump($printedContent);
        }
    } else {
        $printedContent = $standard->prettyPrint([$node]);
        dump($printedContent);
    }
}
