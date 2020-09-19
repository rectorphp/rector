<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait BetterStandardPrinterTrait
{
    /**
     * @var BetterNodeFinder
     */
    protected $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    protected $betterStandardPrinter;

    /**
     * @var SmartFileSystem
     */
    protected $smartFileSystem;

    /**
     * @required
     */
    public function autowireBetterStandardPrinter(
        BetterStandardPrinter $betterStandardPrinter,
        BetterNodeFinder $betterNodeFinder,
        SmartFileSystem $smartFileSystem
    ): void {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->smartFileSystem = $smartFileSystem;
    }

    /**
     * @param Node|Node[]|null $node
     */
    public function print($node): string
    {
        return $this->betterStandardPrinter->print($node);
    }

    /**
     * @param Node|Node[]|null $node
     */
    public function printFile($node): string
    {
        return $this->betterStandardPrinter->prettyPrintFile($node);
    }

    /**
     * @param Node|Node[]|null $node
     */
    public function printWithoutComments($node): string
    {
        return $this->betterStandardPrinter->printWithoutComments($node);
    }

    /**
     * @param Node|Node[]|null $node
     */
    public function printToFile($node, string $filePath): void
    {
        $content = $this->betterStandardPrinter->prettyPrintFile($node);
        $this->smartFileSystem->dumpFile($filePath, $content);
    }

    /**
     * Removes all comments from both nodes
     *
     * @param Node|Node[]|null $firstNode
     * @param Node|Node[]|null $secondNode
     */
    protected function areNodesEqual($firstNode, $secondNode): bool
    {
        return $this->betterStandardPrinter->areNodesEqual($firstNode, $secondNode);
    }

    /**
     * @param Node[] $availableNodes
     */
    protected function isNodeEqual(Node $singleNode, array $availableNodes): bool
    {
        // remove comments, only content is relevant
        $singleNode = clone $singleNode;
        $singleNode->setAttribute(AttributeKey::COMMENTS, null);

        foreach ($availableNodes as $availableNode) {
            // remove comments, only content is relevant
            $availableNode = clone $availableNode;
            $availableNode->setAttribute(AttributeKey::COMMENTS, null);

            if ($this->areNodesEqual($singleNode, $availableNode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Node|Node[] $nodes
     */
    protected function isNodeUsedIn(Node $seekedNode, $nodes): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($nodes, function (Node $node) use ($seekedNode): bool {
            return $this->areNodesEqual($node, $seekedNode);
        });
    }
}
