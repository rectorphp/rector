<?php

declare(strict_types=1);

namespace Rector\MockistaToMockery;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;

final class MockistaDetector
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isInClass(Class_ $class): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($class->stmts, function (Node $node): bool {
            if (! $node instanceof FuncCall) {
                return false;
            }

            return $this->nodeNameResolver->isName($node, 'mock');
        });
    }
}
