<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeTraverser;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use Rector\Contract\Rector\RectorInterface;
use Rector\VersionBonding\PhpVersionedFilter;
final class RectorNodeTraverser extends NodeTraverser
{
    /**
     * @var RectorInterface[]
     */
    private $rectors;
    /**
     * @readonly
     * @var \Rector\VersionBonding\PhpVersionedFilter
     */
    private $phpVersionedFilter;
    /**
     * @var bool
     */
    private $areNodeVisitorsPrepared = \false;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(array $rectors, PhpVersionedFilter $phpVersionedFilter)
    {
        $this->rectors = $rectors;
        $this->phpVersionedFilter = $phpVersionedFilter;
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
        $this->areNodeVisitorsPrepared = \false;
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
        $this->areNodeVisitorsPrepared = \true;
    }
}
