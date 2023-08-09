<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\VersionBonding\PhpVersionedFilter;
use RectorPrefix202308\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
final class RectorNodeTraverser extends NodeTraverser
{
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
     * @var PhpRectorInterface[]
     */
    private $phpRectors = [];
    /**
     * @param RewindableGenerator<PhpRectorInterface>|PhpRectorInterface[] $phpRectors
     */
    public function __construct(iterable $phpRectors, PhpVersionedFilter $phpVersionedFilter)
    {
        $this->phpVersionedFilter = $phpVersionedFilter;
        $this->phpRectors = \is_array($phpRectors) ? $phpRectors : \iterator_to_array($phpRectors);
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
        return parent::traverse($nodes);
    }
    /**
     * @api used in tests to update the active rules
     * @param PhpRectorInterface[] $phpRectors
     */
    public function refreshPhpRectors(array $phpRectors) : void
    {
        $this->phpRectors = $phpRectors;
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
        $activePhpRectors = $this->phpVersionedFilter->filter($this->phpRectors);
        $this->visitors = $this->visitors === [] ? $activePhpRectors : \array_merge($this->visitors, $activePhpRectors);
        $this->areNodeVisitorsPrepared = \true;
    }
}
