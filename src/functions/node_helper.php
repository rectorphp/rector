<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use RectorPrefix202506\Illuminate\Container\Container;
use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use Rector\Console\Style\SymfonyStyleFactory;
use Rector\Util\NodePrinter;
use RectorPrefix202506\Symfony\Component\Console\Output\OutputInterface;
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
if (!\function_exists('dump_node')) {
    /**
     * @param Node|Node[] $node
     */
    function dump_node($node) : void
    {
        $rectorStyle = Container::getInstance()->make(SymfonyStyleFactory::class)->create();
        // we turn up the verbosity so it's visible in tests overriding the
        // default which is to be quite during tests
        $rectorStyle->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        $rectorStyle->newLine();
        $nodePrinter = new NodePrinter($rectorStyle);
        $nodePrinter->printNodes($node);
    }
}
