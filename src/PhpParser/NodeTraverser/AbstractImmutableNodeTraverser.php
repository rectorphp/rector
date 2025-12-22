<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeTraverser;

use LogicException;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use Rector\Exception\ShouldNotHappenException;
abstract class AbstractImmutableNodeTraverser implements NodeTraverserInterface
{
    /**
     * @var list<NodeVisitor> Visitors
     */
    protected array $visitors = [];
    /**
     * @var bool Whether traversal should be stopped
     */
    protected bool $stopTraversal;
    /**
     * Create a traverser with the given visitors.
     *
     * @param NodeVisitor ...$visitors Node visitors
     */
    public function __construct(NodeVisitor ...$visitors)
    {
        $this->visitors = $visitors;
    }
    /**
     * Adds a visitor.
     *
     * @param NodeVisitor $visitor Visitor to add
     */
    public function addVisitor(NodeVisitor $visitor): void
    {
        $this->visitors[] = $visitor;
    }
    public function removeVisitor(NodeVisitor $visitor): void
    {
        throw new ShouldNotHappenException('The immutable node traverser does not support removing visitors.');
    }
    /**
     * Traverses an array of nodes using the registered visitors.
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return Node[] Traversed array of nodes
     */
    public function traverse(array $nodes): array
    {
        $this->stopTraversal = \false;
        foreach ($this->visitors as $visitor) {
            if (null !== $return = $visitor->beforeTraverse($nodes)) {
                $nodes = $return;
            }
        }
        $nodes = $this->traverseArray($nodes);
        for ($i = \count($this->visitors) - 1; $i >= 0; --$i) {
            $visitor = $this->visitors[$i];
            if (null !== $return = $visitor->afterTraverse($nodes)) {
                $nodes = $return;
            }
        }
        return $nodes;
    }
    /**
     * @return NodeVisitor[]
     */
    abstract public function getVisitorsForNode(Node $node): array;
    protected function traverseNode(Node $node): void
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
            $traverseChildren = \true;
            $visitorIndex = -1;
            $currentNodeVisitors = $this->getVisitorsForNode($subNode);
            foreach ($currentNodeVisitors as $visitorIndex => $visitor) {
                $return = $visitor->enterNode($subNode);
                if ($return !== null) {
                    if ($return instanceof Node) {
                        $originalSubNodeClass = get_class($subNode);
                        $this->ensureReplacementReasonable($subNode, $return);
                        $subNode = $return;
                        $node->{$name} = $return;
                        if ($originalSubNodeClass !== get_class($subNode)) {
                            // stop traversing as node type changed and visitors won't work
                            continue 2;
                        }
                    } elseif ($return === NodeVisitor::DONT_TRAVERSE_CHILDREN) {
                        $traverseChildren = \false;
                    } elseif ($return === NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN) {
                        $traverseChildren = \false;
                        break;
                    } elseif ($return === NodeVisitor::STOP_TRAVERSAL) {
                        $this->stopTraversal = \true;
                        break 2;
                    } elseif ($return === NodeVisitor::REPLACE_WITH_NULL) {
                        $node->{$name} = null;
                        continue 2;
                    } else {
                        throw new LogicException('enterNode() returned invalid value of type ' . gettype($return));
                    }
                }
            }
            if ($traverseChildren) {
                $this->traverseNode($subNode);
                if ($this->stopTraversal) {
                    break;
                }
            }
            for (; $visitorIndex >= 0; --$visitorIndex) {
                $visitor = $currentNodeVisitors[$visitorIndex];
                $return = $visitor->leaveNode($subNode);
                if ($return !== null) {
                    if ($return instanceof Node) {
                        $this->ensureReplacementReasonable($subNode, $return);
                        $subNode = $return;
                        $node->{$name} = $return;
                    } elseif ($return === NodeVisitor::STOP_TRAVERSAL) {
                        $this->stopTraversal = \true;
                        break 2;
                    } elseif ($return === NodeVisitor::REPLACE_WITH_NULL) {
                        $node->{$name} = null;
                        break;
                    } elseif (\is_array($return)) {
                        throw new LogicException('leaveNode() may only return an array if the parent structure is an array');
                    } else {
                        throw new LogicException('leaveNode() returned invalid value of type ' . gettype($return));
                    }
                }
            }
        }
    }
    /**
     * @param Node[] $nodes
     * @return array Result of traversal (may be original array or changed one)
     */
    protected function traverseArray(array $nodes): array
    {
        $doNodes = [];
        foreach ($nodes as $i => $node) {
            if (!$node instanceof Node) {
                if (\is_array($node)) {
                    throw new LogicException('Invalid node structure: Contains nested arrays');
                }
                continue;
            }
            $traverseChildren = \true;
            $visitorIndex = -1;
            $currentNodeVisitors = $this->getVisitorsForNode($node);
            foreach ($currentNodeVisitors as $visitorIndex => $visitor) {
                $return = $visitor->enterNode($node);
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
                    } elseif ($return === NodeVisitor::DONT_TRAVERSE_CHILDREN) {
                        $traverseChildren = \false;
                    } elseif ($return === NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN) {
                        $traverseChildren = \false;
                        break;
                    } elseif ($return === NodeVisitor::STOP_TRAVERSAL) {
                        $this->stopTraversal = \true;
                        break 2;
                    } elseif ($return === NodeVisitor::REPLACE_WITH_NULL) {
                        throw new LogicException('REPLACE_WITH_NULL can not be used if the parent structure is an array');
                    } else {
                        throw new LogicException('enterNode() returned invalid value of type ' . gettype($return));
                    }
                }
            }
            if ($traverseChildren) {
                $this->traverseNode($node);
                if ($this->stopTraversal) {
                    break;
                }
            }
            for (; $visitorIndex >= 0; --$visitorIndex) {
                $visitor = $currentNodeVisitors[$visitorIndex];
                $return = $visitor->leaveNode($node);
                if ($return !== null) {
                    if ($return instanceof Node) {
                        $this->ensureReplacementReasonable($node, $return);
                        $nodes[$i] = $node = $return;
                    } elseif (\is_array($return)) {
                        $doNodes[] = [$i, $return];
                        break;
                    } elseif ($return === NodeVisitor::REMOVE_NODE) {
                        $doNodes[] = [$i, []];
                        break;
                    } elseif ($return === NodeVisitor::STOP_TRAVERSAL) {
                        $this->stopTraversal = \true;
                        break 2;
                    } elseif ($return === NodeVisitor::REPLACE_WITH_NULL) {
                        throw new LogicException('REPLACE_WITH_NULL can not be used if the parent structure is an array');
                    } else {
                        throw new LogicException('leaveNode() returned invalid value of type ' . gettype($return));
                    }
                }
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
