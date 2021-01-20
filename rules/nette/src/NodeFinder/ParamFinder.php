<?php

declare(strict_types=1);

namespace Rector\Nette\NodeFinder;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Param;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ParamFinder
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(BetterNodeFinder $betterNodeFinder, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @param Node|Node[] $nodeHaystack
     */
    public function isInAssign($nodeHaystack, Param $param): bool
    {
        $variable = $param->var;

        return (bool) $this->betterNodeFinder->find($nodeHaystack, function (Node $node) use ($variable): bool {
            $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (! $parent instanceof Assign) {
                return false;
            }

            return $this->betterStandardPrinter->areNodesEqual($node, $variable);
        });
    }
}
