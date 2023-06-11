<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\VersionBonding\PhpVersionedFilter;
final class RectorNodeTraverser extends NodeTraverser
{
    /**
     * @var PhpRectorInterface[]
     */
    private $phpRectors;
    /**
     * @var \Rector\VersionBonding\PhpVersionedFilter
     */
    private $phpVersionedFilter;
    /**
     * @var bool
     */
    private $areNodeVisitorsPrepared = \false;
    /** @var PhpRectorInterface[]|NodeVisitor[] */
    private $activePhpRectors = [];
    /**
     * @param PhpRectorInterface[] $phpRectors
     */
    public function __construct(array $phpRectors, PhpVersionedFilter $phpVersionedFilter)
    {
        $this->phpRectors = $phpRectors;
        $this->phpVersionedFilter = $phpVersionedFilter;
        parent::__construct();
    }
    /**
     * @template TNode as Node
     * @param TNode[] $nodes
     * @return TNode[]
     */
    public function traverse(array $nodes) : array
    {
        $this->prepareNodeVisitors();
        foreach ($this->activePhpRectors as $activePhpRector) {
            $this->visitors = [$activePhpRector];
            // call parent::traverse() on loop to ensure
            // stopTraversal always reset to false before run on next Rector rule
            $nodes = parent::traverse($nodes);
        }
        return $nodes;
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
        $activePhpRectors = $this->phpVersionedFilter->filter($this->phpRectors);
        $this->activePhpRectors = \array_merge($this->visitors, $activePhpRectors);
        $this->areNodeVisitorsPrepared = \true;
    }
}
