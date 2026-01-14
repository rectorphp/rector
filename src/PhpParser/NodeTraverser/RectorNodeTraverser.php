<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeTraverser;

use LogicException;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use Rector\Configuration\ConfigurationRuleFilter;
use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\PhpParser\Node\FileNode;
use Rector\VersionBonding\PhpVersionedFilter;
use RectorPrefix202601\Webmozart\Assert\Assert;
/**
 *  Based on native NodeTraverser class, but heavily customized for Rector needs.
 *
 *  The main differences are:
 *  - no leaveNode(), as we do all in enterNode() that calls refactor() method
 *  - cached visitors per node class for performance, e.g. when we find rules for Class_ node, they're cached for next time
 *  - immutability features, register Rector rules once, then use; no changes on the fly
 *
 * @see \Rector\Tests\PhpParser\NodeTraverser\RectorNodeTraverserTest
 * @internal No BC promise on this class, it might change any time.
 */
final class RectorNodeTraverser implements NodeTraverserInterface
{
    /**
     * @var RectorInterface[]
     */
    private array $rectors;
    /**
     * @readonly
     */
    private PhpVersionedFilter $phpVersionedFilter;
    /**
     * @readonly
     */
    private ConfigurationRuleFilter $configurationRuleFilter;
    /**
     * @var RectorInterface[]
     */
    private array $visitors = [];
    private bool $stopTraversal;
    private bool $areNodeVisitorsPrepared = \false;
    /**
     * @var array<class-string<Node>, RectorInterface[]>
     */
    private array $visitorsPerNodeClass = [];
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(array $rectors, PhpVersionedFilter $phpVersionedFilter, ConfigurationRuleFilter $configurationRuleFilter)
    {
        $this->rectors = $rectors;
        $this->phpVersionedFilter = $phpVersionedFilter;
        $this->configurationRuleFilter = $configurationRuleFilter;
    }
    public function addVisitor(NodeVisitor $visitor): void
    {
        throw new ShouldNotHappenException('The immutable node traverser does not support adding visitors.');
    }
    public function removeVisitor(NodeVisitor $visitor): void
    {
        throw new ShouldNotHappenException('The immutable node traverser does not support removing visitors.');
    }
    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        $this->prepareNodeVisitors();
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
     * @param RectorInterface[] $rectors
     * @api used in tests to update the active rules
     *
     * @internal Used only in Rector core, not supported outside. Might change any time.
     */
    public function refreshPhpRectors(array $rectors): void
    {
        Assert::allIsInstanceOf($rectors, RectorInterface::class);
        $this->rectors = $rectors;
        $this->visitors = [];
        $this->visitorsPerNodeClass = [];
        $this->areNodeVisitorsPrepared = \false;
        $this->prepareNodeVisitors();
    }
    /**
     * @return RectorInterface[]
     *
     * @api used in tests
     */
    public function getVisitorsForNode(Node $node): array
    {
        $nodeClass = get_class($node);
        if (!isset($this->visitorsPerNodeClass[$nodeClass])) {
            $this->visitorsPerNodeClass[$nodeClass] = [];
            /** @var RectorInterface $visitor */
            foreach ($this->visitors as $visitor) {
                foreach ($visitor->getNodeTypes() as $nodeType) {
                    // BC layer matching
                    if ($nodeType === FileWithoutNamespace::class && $nodeClass === FileNode::class) {
                        $this->visitorsPerNodeClass[$nodeClass][] = $visitor;
                        continue;
                    }
                    if (is_a($nodeClass, $nodeType, \true)) {
                        $this->visitorsPerNodeClass[$nodeClass][] = $visitor;
                        continue 2;
                    }
                }
            }
        }
        return $this->visitorsPerNodeClass[$nodeClass];
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
            $traverseChildren = \true;
            $currentNodeVisitors = $this->getVisitorsForNode($subNode);
            foreach ($currentNodeVisitors as $currentNodeVisitor) {
                $return = $currentNodeVisitor->enterNode($subNode);
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
        }
    }
    /**
     * @param Node[] $nodes
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
            $traverseChildren = \true;
            $currentNodeVisitors = $this->getVisitorsForNode($node);
            foreach ($currentNodeVisitors as $currentNodeVisitor) {
                $return = $currentNodeVisitor->enterNode($node);
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
    /**
     * This must happen after $this->configuration is set after ProcessCommand::execute() is run, otherwise we get default false positives.
     *
     * This should be removed after https://github.com/rectorphp/rector/issues/5584 is resolved
     */
    private function prepareNodeVisitors(): void
    {
        if ($this->areNodeVisitorsPrepared) {
            return;
        }
        // filer out by version
        $this->visitors = $this->phpVersionedFilter->filter($this->rectors);
        // filter by configuration
        $this->visitors = $this->configurationRuleFilter->filter($this->visitors);
        $this->areNodeVisitorsPrepared = \true;
    }
}
