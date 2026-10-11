<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeTraverser;

use LogicException;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitor;
use Rector\Application\RectorRegistry;
use Rector\Rector\RectorRunner;
final class RectorNodeTraverser
{
    /**
     * @readonly
     */
    private RectorRunner $rectorRunner;
    /**
     * @readonly
     */
    private RectorRegistry $rectorRegistry;
    private bool $stopTraversal;
    public function __construct(RectorRunner $rectorRunner, RectorRegistry $rectorRegistry)
    {
        $this->rectorRunner = $rectorRunner;
        $this->rectorRegistry = $rectorRegistry;
    }
    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        $this->stopTraversal = \false;
        return $this->traverseArray($nodes);
    }
    private function traverseNode(Node $node): void
    {
        foreach ($node->getSubNodeNames() as $name) {
            $subNode = $node->{$name};
            if (\is_array($subNode)) {
                $node->{$name} = $this->traverseArray($subNode);
                if ($this->stopTraversal) {
                    break;
                }
                continue;
            }
            if (!$subNode instanceof Node) {
                continue;
            }
            $currentNodeVisitors = $this->rectorRegistry->forNode($subNode);
            foreach ($currentNodeVisitors as $currentNodeVisitor) {
                $return = $this->rectorRunner->run($currentNodeVisitor, $subNode);
                if ($return === null) {
                    continue;
                }
                if ($return instanceof Node) {
                    $originalSubNodeClass = get_class($subNode);
                    $this->ensureReplacementReasonable($subNode, $return);
                    $subNode = $return;
                    $node->{$name} = $return;
                    if ($originalSubNodeClass !== get_class($subNode)) {
                        // stop traversing as node type changed and visitors won't work
                        continue 2;
                    }
                } else {
                    throw new LogicException('RectorRunner::run() returned invalid value of type ' . gettype($return));
                }
            }
            $this->traverseNode($subNode);
            if ($this->stopTraversal) {
                break;
            }
        }
    }
    /**
     * @param array<Node|null> $nodes The null can be in case of empty list(, , )
     * @return Node[]
     */
    private function traverseArray(array $nodes): array
    {
        $doNodes = [];
        foreach ($nodes as $i => $node) {
            if (!$node instanceof Node) {
                if (\is_array($node)) {
                    throw new LogicException('Invalid node structure: Contains nested arrays');
                }
                continue;
            }
            $currentNodeVisitors = $this->rectorRegistry->forNode($node);
            foreach ($currentNodeVisitors as $currentNodeVisitor) {
                $return = $this->rectorRunner->run($currentNodeVisitor, $node);
                if ($return !== null) {
                    if ($return instanceof Node) {
                        $originalNodeNodeClass = get_class($node);
                        $this->ensureReplacementReasonable($node, $return);
                        $nodes[$i] = $node = $return;
                        if ($originalNodeNodeClass !== get_class($return)) {
                            // stop traversing as node type changed and visitors won't work
                            continue 2;
                        }
                    } elseif (\is_array($return)) {
                        $doNodes[] = [$i, $return];
                        continue 2;
                    } elseif ($return === NodeVisitor::REMOVE_NODE) {
                        $doNodes[] = [$i, []];
                        continue 2;
                    } else {
                        throw new LogicException('RectorRunner::run() returned invalid value of type ' . gettype($return));
                    }
                }
            }
            $this->traverseNode($node);
            if ($this->stopTraversal) {
                break;
            }
        }
        if ($doNodes !== []) {
            while ([$i, $replace] = array_pop($doNodes)) {
                array_splice($nodes, $i, 1, $replace);
            }
        }
        return $nodes;
    }
    private function ensureReplacementReasonable(Node $old, Node $new): void
    {
        if ($old instanceof Stmt) {
            if ($new instanceof Expr) {
                throw new LogicException(sprintf('Trying to replace statement (%s) ', $old->getType()) . sprintf('with expression (%s). Are you missing a ', $new->getType()) . 'Stmt_Expression wrapper?');
            }
            return;
        }
        if ($new instanceof Stmt) {
            throw new LogicException(sprintf('Trying to replace expression (%s) ', $old->getType()) . sprintf('with statement (%s)', $new->getType()));
        }
    }
}
