<?php

declare (strict_types=1);
namespace Rector\PostRector\Guard;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Namespace_;
use Rector\PhpParser\Node\BetterNodeFinder;
final class AddUseStatementGuard
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var array<string, bool>
     */
    private $shouldTraverseOnFiles = [];
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param Stmt[] $stmts
     */
    public function shouldTraverse(array $stmts, string $filePath) : bool
    {
        if (isset($this->shouldTraverseOnFiles[$filePath])) {
            return $this->shouldTraverseOnFiles[$filePath];
        }
        $totalNamespaces = 0;
        // just loop the first level stmts to locate namespace to improve performance
        // as namespace is always on first level
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Namespace_) {
                ++$totalNamespaces;
            }
            // skip if 2 namespaces are present
            if ($totalNamespaces === 2) {
                return $this->shouldTraverseOnFiles[$filePath] = \false;
            }
        }
        return $this->shouldTraverseOnFiles[$filePath] = !$this->betterNodeFinder->hasInstancesOf($stmts, [InlineHTML::class]);
    }
}
