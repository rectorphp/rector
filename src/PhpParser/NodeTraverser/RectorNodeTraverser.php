<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use Rector\Configuration\ConfigurationRuleFilter;
use Rector\Contract\Rector\RectorInterface;
use Rector\VersionBonding\PhpVersionedFilter;
final class RectorNodeTraverser extends NodeTraverser
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
    private bool $areNodeVisitorsPrepared = \false;
    /**
     * @var array<class-string<Node>,RectorInterface[]>
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
        parent::__construct();
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function traverse(array $nodes) : array
    {
        $this->prepareNodeVisitors();
        return parent::traverse($nodes);
    }
    /**
     * @param RectorInterface[] $rectors
     * @api used in tests to update the active rules
     */
    public function refreshPhpRectors(array $rectors) : void
    {
        $this->rectors = $rectors;
        $this->visitors = [];
        $this->visitorsPerNodeClass = [];
        $this->areNodeVisitorsPrepared = \false;
    }
    /**
     * We return the list of visitors (rector rules) that can be applied to each node class
     * This list is cached so that we don't need to continually check if a rule can be applied to a node
     *
     * @return NodeVisitor[]
     */
    public function getVisitorsForNode(Node $node) : array
    {
        $nodeClass = \get_class($node);
        if (!isset($this->visitorsPerNodeClass[$nodeClass])) {
            $this->visitorsPerNodeClass[$nodeClass] = [];
            foreach ($this->visitors as $visitor) {
                \assert($visitor instanceof RectorInterface);
                foreach ($visitor->getNodeTypes() as $nodeType) {
                    if (\is_a($nodeClass, $nodeType, \true)) {
                        $this->visitorsPerNodeClass[$nodeClass][] = $visitor;
                        continue 2;
                    }
                }
            }
        }
        return $this->visitorsPerNodeClass[$nodeClass];
    }
    /**
     * This must happen after $this->configuration is set after ProcessCommand::execute() is run,
     * otherwise we get default false positives.
     *
     * This hack should be removed after https://github.com/rectorphp/rector/issues/5584 is resolved
     */
    private function prepareNodeVisitors() : void
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
