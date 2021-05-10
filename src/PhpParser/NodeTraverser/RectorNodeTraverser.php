<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeTraverser;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class RectorNodeTraverser extends \PhpParser\NodeTraverser
{
    /**
     * @var bool
     */
    private $areNodeVisitorsPrepared = \false;
    /**
     * @var mixed[]
     */
    private $phpRectors;
    /**
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    /**
     * @param PhpRectorInterface[] $phpRectors
     */
    public function __construct(array $phpRectors, \PhpParser\NodeFinder $nodeFinder)
    {
        $this->phpRectors = $phpRectors;
        $this->nodeFinder = $nodeFinder;
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function traverse(array $nodes) : array
    {
        $this->prepareNodeVisitors();
        $hasNamespace = (bool) $this->nodeFinder->findFirstInstanceOf($nodes, \PhpParser\Node\Stmt\Namespace_::class);
        if (!$hasNamespace && $nodes !== []) {
            $fileWithoutNamespace = new \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace($nodes);
            return parent::traverse([$fileWithoutNamespace]);
        }
        return parent::traverse($nodes);
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
        foreach ($this->phpRectors as $phpRector) {
            $this->addVisitor($phpRector);
        }
        $this->areNodeVisitorsPrepared = \true;
    }
}
