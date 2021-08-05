<?php

declare (strict_types=1);
namespace Rector\NodeCollector\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeCollector\NodeCollector\NodeRepository;
final class NodeCollectorNodeVisitor extends \PhpParser\NodeVisitorAbstract
{
    /**
     * @var \Rector\NodeCollector\NodeCollector\NodeRepository
     */
    private $nodeRepository;
    public function __construct(\Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }
    public function enterNode(\PhpParser\Node $node)
    {
        if ($node instanceof \PhpParser\Node\Stmt\Class_) {
            $this->nodeRepository->collectClass($node);
        }
        return null;
    }
}
