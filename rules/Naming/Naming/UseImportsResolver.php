<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class UseImportsResolver
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return Use_[]
     */
    public function resolveForNode(\PhpParser\Node $node) : array
    {
        $namespace = $this->betterNodeFinder->findParentByTypes($node, [\PhpParser\Node\Stmt\Namespace_::class, \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class]);
        if (!$namespace instanceof \PhpParser\Node) {
            return [];
        }
        return $this->betterNodeFinder->findInstanceOf($namespace, \PhpParser\Node\Stmt\Use_::class);
    }
}
