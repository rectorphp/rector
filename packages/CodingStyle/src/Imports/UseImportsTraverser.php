<?php declare(strict_types=1);

namespace Rector\CodingStyle\Imports;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Use_;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

final class UseImportsTraverser
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(NameResolver $nameResolver, CallableNodeTraverser $callableNodeTraverser)
    {
        $this->nameResolver = $nameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    /**
     * @param Stmt[] $stmts
     */
    public function traverserStmts(array $stmts, callable $callable, int $type = Use_::TYPE_NORMAL): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use ($callable, $type) {
            if (! $node instanceof Use_) {
                return null;
            }

            // only import uses
            if ($node->type !== $type) {
                return null;
            }

            foreach ($node->uses as $useUse) {
                $name = $this->nameResolver->getName($useUse);
                $callable($useUse, $name);
            }
        });
    }
}
